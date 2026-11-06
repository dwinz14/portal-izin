<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\JabatanExternal;
use App\Enums\WorkSchedule;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterPositionController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];
    private const SORTABLE = ['nama_jabatan', 'work_schedule', 'users_count'];

    /**
     * Display a listing of the positions.
     */
    public function index(Request $request)
    {
        return view('admin.positions.index', $this->listData($request));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create()
    {
        return view('admin.positions.create');
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan'  => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:positions,nama_jabatan',
            'work_schedule' => ['required', Rule::enum(WorkSchedule::class)],
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_jabatan = strtolower(trim($request->nama_jabatan));

        Position::create([
            'nama_jabatan'  => $nama_jabatan,
            'work_schedule' => WorkSchedule::from($request->work_schedule),
        ]);

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position)
    {
        return view('admin.positions.edit', compact('position'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(Request $request, Position $position)
    {
        $request->validate([
            'nama_jabatan'  => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:positions,nama_jabatan,' . $position->id,
            'work_schedule' => ['required', Rule::enum(WorkSchedule::class)],
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_jabatan = strtolower(trim($request->nama_jabatan));

        $oldSchedule = $position->work_schedule ?? WorkSchedule::Weekday;
        $newSchedule = WorkSchedule::from($request->work_schedule);

        $position->update([
            'nama_jabatan'  => $nama_jabatan,
            'work_schedule' => $newSchedule,
        ]);

        // Pola hari kerja memengaruhi perhitungan cuti, jadi perubahannya dicatat.
        if ($oldSchedule !== $newSchedule) {
            ActivityLogger::log(
                'admin.position_schedule_changed',
                'Mengubah pola hari kerja jabatan ' . strtoupper($nama_jabatan)
                    . " dari \"{$oldSchedule->label()}\" menjadi \"{$newSchedule->label()}\"",
                $position,
                [
                    'jabatan' => strtoupper($nama_jabatan),
                    'dari'    => $oldSchedule->value,
                    'ke'      => $newSchedule->value,
                ]
            );
        }

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil dihapus.');
    }

    /**
     * Tampilkan preview jabatan dari DB karyawan yang belum ada di positions.
     */
    public function sync()
    {
        try {
            $newJabatan = JabatanExternal::getNewOnly();
        } catch (\Exception $e) {
            Log::error('Sync jabatan gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.positions.index')
                ->with('error', 'Koneksi ke database karyawan gagal. Coba beberapa saat lagi.');
        }

        return view('admin.positions.index', $this->listData($request) + compact('newJabatan'));
    }

    /**
     * Insert satu jabatan dari hasil sync ke tabel positions.
     */
    public function insertFromExternal(Request $request)
    {
        $request->validate([
            'nama_jabatan' => ['required', 'string', 'max:255'],
        ]);

        $nama = strtoupper(trim($request->nama_jabatan));

        // Guard: cek duplikat sekali lagi di backend
        $sudahAda = Position::whereRaw('UPPER(TRIM(nama_jabatan)) = ?', [$nama])->exists();

        if ($sudahAda) {
            return redirect()
                ->route('admin.positions.sync')
                ->with('error', "Jabatan \"{$nama}\" sudah ada di daftar jabatan.");
        }

        try {
            // Verifikasi bahwa jabatan ini memang ada di DB karyawan
            $valid = JabatanExternal::getAllNormalized()->contains($nama);

            if (! $valid) {
                return redirect()
                    ->route('admin.positions.sync')
                    ->with('error', 'Jabatan tidak ditemukan di database karyawan.');
            }
        } catch (\Exception $e) {
            Log::error('Insert jabatan external gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.positions.sync')
                ->with('error', 'Koneksi ke database karyawan gagal saat verifikasi.');
        }

        Position::create(['nama_jabatan' => $nama]);

        return redirect()
            ->route('admin.positions.sync')
            ->with('success', "Jabatan \"{$nama}\" berhasil ditambahkan.");
    }

    /**
     * Insert semua jabatan baru dari DB karyawan sekaligus.
     */
    public function insertAllFromExternal()
    {
        try {
            $newJabatan = JabatanExternal::getNewOnly();
        } catch (\Exception $e) {
            Log::error('Insert all jabatan external gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.positions.sync')
                ->with('error', 'Koneksi ke database karyawan gagal.');
        }

        if ($newJabatan->isEmpty()) {
            return redirect()
                ->route('admin.positions.sync')
                ->with('info', 'Tidak ada jabatan baru yang perlu ditambahkan.');
        }

        $inserted = 0;
        foreach ($newJabatan as $nama) {
            $sudahAda = Position::whereRaw('UPPER(TRIM(nama_jabatan)) = ?', [$nama])->exists();
            if (! $sudahAda) {
                Position::create(['nama_jabatan' => $nama]);
                $inserted++;
            }
        }

        return redirect()
            ->route('admin.positions.sync')
            ->with('success', "{$inserted} jabatan baru berhasil ditambahkan.");
    }

    /**
     * Data daftar jabatan
     */
    private function listData(Request $request): array
    {
        // Semua input dari query string divalidasi manual (whitelist), nilai aneh jatuh ke default.
        $rawSearch = $request->query('search');
        $search    = is_string($rawSearch) ? trim($rawSearch) : '';

        $rawSchedule = $request->query('schedule');
        $schedule    = is_string($rawSchedule) ? WorkSchedule::tryFrom($rawSchedule) : null;

        $rawSort   = $request->query('sort');
        $sort      = is_string($rawSort) && in_array($rawSort, self::SORTABLE, true) ? $rawSort : 'nama_jabatan';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 10;
        }

        $positions = Position::query()
            ->withCount('users')
            ->when($search !== '', function ($q) use ($search) {
                // Escape % dan _ supaya diperlakukan sebagai teks biasa, bukan wildcard
                $q->where('nama_jabatan', 'like', '%' . addcslashes($search, '%_\\') . '%');
            })
            ->when($schedule, fn($q) => $q->where('work_schedule', $schedule->value))
            ->orderBy($sort, $direction)
            ->orderBy('id') // tie-breaker agar urutan antar halaman stabil
            ->paginate($perPage)
            ->withQueryString();

        return [
            'positions'      => $positions,
            'search'         => $search,
            'scheduleFilter' => $schedule?->value,
            'sort'           => $sort,
            'direction'      => $direction,
            'perPage'        => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ];
    }
}
