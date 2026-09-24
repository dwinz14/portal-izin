<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\JabatanExternal;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MasterPositionController extends Controller
{

    /**
     * Display a listing of the positions.
     */
    public function index(Request $request)
    {

        $positions = Position::query()
            ->paginate(10);

        return view('admin.positions.index', compact('positions'));
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
            'nama_jabatan' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:positions,nama_jabatan',
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_jabatan = strtolower(trim($request->nama_jabatan));

        Position::create([
            'nama_jabatan' => $nama_jabatan,
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
            'nama_jabatan' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:positions,nama_jabatan,' . $position->id,
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_jabatan = strtolower(trim($request->nama_jabatan));

        $position->update([
            'nama_jabatan' => $nama_jabatan,
        ]);

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

        $positions = Position::query()->paginate(10);

        return view('admin.positions.index', compact('positions', 'newJabatan'));
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
}
