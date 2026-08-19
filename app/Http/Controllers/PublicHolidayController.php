<?php

namespace App\Http\Controllers;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PublicHolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);

        $holidays = PublicHoliday::where('year', $year)
            ->orderBy('date')
            ->paginate(15)
            ->withQueryString();

        $nationalCount = PublicHoliday::where('year', $year)
            ->where('is_active', true)
            ->nationalHolidays()
            ->count();

        $jointLeaves = PublicHoliday::where('year', $year)
            ->where('is_active', true)
            ->jointLeaves()
            ->get();

        $summary = (object) [
            'national_count' => $nationalCount,
            'joint_count' => $jointLeaves->count(),
            'joint_weekday_count' => $jointLeaves->filter(fn ($h) => $h->date->isWeekday())->count(),
        ];

        $years = PublicHoliday::selectRaw('DISTINCT year')->orderBy('year', 'desc')->pluck('year');

        return view('hrd.holidays.index', compact('holidays', 'year', 'summary', 'years'));
    }

    public function create()
    {
        return view('hrd.holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateHoliday($request);

        $holiday = PublicHoliday::updateOrCreate(
            ['date' => $validated['date']],
            [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]
        );

        PublicHoliday::clearCacheForYear($holiday->year);

        return redirect()->route('hrd.holidays.index', ['year' => $holiday->year])
            ->with('success', 'Hari libur "'.$holiday->name.'" berhasil disimpan.');
    }

    public function edit(PublicHoliday $publicHoliday)
    {
        return view('hrd.holidays.edit', ['holiday' => $publicHoliday]);
    }

    public function update(Request $request, PublicHoliday $publicHoliday)
    {
        $validated = $this->validateHoliday($request, $publicHoliday->id);

        $publicHoliday->update([
            'date' => $validated['date'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
        ]);

        PublicHoliday::clearCacheForYear($publicHoliday->year);

        return redirect()->route('hrd.holidays.index', ['year' => $publicHoliday->year])
            ->with('success', 'Hari libur "'.$publicHoliday->name.'" berhasil diperbarui.');
    }

    public function destroy(PublicHoliday $publicHoliday)
    {
        $year = $publicHoliday->year;
        $name = $publicHoliday->name;

        $publicHoliday->delete();

        PublicHoliday::clearCacheForYear($year);

        return redirect()->route('hrd.holidays.index', ['year' => $year])
            ->with('success', 'Hari libur "'.$name.'" berhasil dihapus.');
    }

    /**
     * Aktifkan/nonaktifkan hari libur. Data hasil sinkronisasi API
     * masuk sebagai draft (nonaktif) dan perlu diaktifkan setelah review.
     */
    public function toggle(PublicHoliday $publicHoliday)
    {
        $publicHoliday->update([
            'is_active' => ! $publicHoliday->is_active,
        ]);

        PublicHoliday::clearCacheForYear($publicHoliday->year);

        $status = $publicHoliday->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Hari libur \"{$publicHoliday->name}\" berhasil {$status}.");
    }

    /**
     * Bulk import: paste baris "tanggal | nama | tipe" (boleh dari Excel).
     * Tipe opsional: nasional / national_holiday / cuti_bersama / joint_leave.
     * Baris yang tanggalnya sudah ada akan di-update (tidak dobel).
     */
    public function import(Request $request)
    {
        $request->validate([
            'lines' => 'required|string',
        ]);

        $lines = preg_split('/\r\n|\r|\n/', trim($request->lines));
        $imported = 0;
        $failed = [];

        DB::transaction(function () use ($lines, &$imported, &$failed) {
            foreach ($lines as $index => $rawLine) {
                $lineNo = $index + 1;
                $line = trim($rawLine);

                if ($line === '') {
                    continue;
                }

                // Pemisah: tab (copy Excel) atau "|"
                $parts = preg_split('/[\t|]+/', $line, 3);

                if (count($parts) < 2) {
                    $failed[] = "Baris {$lineNo}: format salah. Gunakan: tanggal | nama | tipe";

                    continue;
                }

                try {
                    $date = Carbon::parse(trim($parts[0]))->format('Y-m-d');
                } catch (\Throwable) {
                    $failed[] = "Baris {$lineNo}: tanggal tidak valid ('{$parts[0]}')";

                    continue;
                }

                $name = trim($parts[1]);
                $typeRaw = strtolower(trim($parts[2] ?? 'nasional'));

                $type = match ($typeRaw) {
                    'cuti_bersama', 'joint_leave', 'cuti bersama', 'cuti-bersama' => 'joint_leave',
                    'nasional', 'national', 'national_holiday', 'libur nasional' => 'national_holiday',
                    default => null,
                };

                if ($type === null) {
                    $failed[] = "Baris {$lineNo}: tipe tidak dikenal ('{$parts[2]}'). Gunakan: nasional / cuti_bersama";

                    continue;
                }

                if ($name === '') {
                    $failed[] = "Baris {$lineNo}: nama hari libur kosong";

                    continue;
                }

                PublicHoliday::updateOrCreate(
                    ['date' => $date],
                    [
                        'name' => $name,
                        'type' => $type,
                        'is_active' => true,
                    ]
                );

                $imported++;
            }
        });

        if ($imported > 0) {
            // Bersihkan cache semua tahun yang mungkin terkena import
            foreach (PublicHoliday::selectRaw('DISTINCT year')->pluck('year') as $y) {
                PublicHoliday::clearCacheForYear($y);
            }
        }

        $message = "Import berhasil: {$imported} hari libur.";

        if (! empty($failed)) {
            $message .= ' '.count($failed).' baris gagal: '.implode('; ', array_slice($failed, 0, 5))
                .(count($failed) > 5 ? ' (dan '.(count($failed) - 5).' lainnya)' : '');
        }

        return back()->with($failed ? 'error' : 'success', $message);
    }

    private function validateHoliday(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date',
                Rule::unique('public_holidays', 'date')->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['national_holiday', 'joint_leave'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        return $validated;
    }
}
