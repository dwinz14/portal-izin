<?php

namespace App\Http\Controllers;

use App\Exports\HolidayTemplateExport;
use App\Imports\HolidayExcelImport;
use App\Models\PublicHoliday;
use App\Services\HolidayImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PublicHolidayController extends Controller
{
    public function __construct(protected HolidayImportService $importService) {}

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

    /**
     * Unduh template Excel untuk input massal hari libur.
     */
    public function template(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        return Excel::download(new HolidayTemplateExport($year), "template-hari-libur-{$year}.xlsx");
    }

    /**
     * Tahap 1 (Excel): upload file -> parse -> preview untuk verifikasi.
     * Data belum disimpan sampai user mengkonfirmasi.
     */
    public function importExcelPreview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:1024'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return back()->with('error', 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv.');
        }

        try {
            $importer = new HolidayExcelImport;
            Excel::import($importer, $file);
            $rawRows = $importer->rows();
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: '.$e->getMessage());
        }

        if (empty($rawRows)) {
            return back()->with('error', 'File tidak berisi baris data. Periksa kembali file Anda.');
        }

        $entries = $this->importService->normalize($rawRows);
        $entries = $this->withSourceDescription($entries, 'Import via Excel: '.$file->getClientOriginalName());

        $source = 'Excel — '.$file->getClientOriginalName();
        $this->storePreview($entries, $source);

        return view('hrd.holidays.import-preview', [
            'entries' => $entries,
            'source' => $source,
            'year' => (int) $request->get('year', now()->year),
        ]);
    }

    /**
     * Tahap 1 (API): tarik data dari API hari libur -> preview untuk verifikasi.
     * Data disimpan ke session sampai user mengkonfirmasi.
     */
    public function importApiPreview(Request $request)
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $year = (int) $request->year;
        $url = config('services.holiday_api.url', 'https://api-hari-libur.vercel.app/api');

        try {
            $response = Http::timeout(15)->get($url, ['year' => $year]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal terhubung ke API hari libur: '.$e->getMessage());
        }

        if ($response->failed()) {
            return back()->with('error', 'API hari libur merespons dengan status '.$response->status().'. Coba lagi nanti.');
        }

        $payload = $response->json();
        $items = $payload['data'] ?? $payload;

        if (! is_array($items) || count($items) === 0) {
            return back()->with('error', "Tidak ada data hari libur dari API untuk tahun {$year}.");
        }

        $rawRows = collect($items)->map(fn ($item) => [
            'date' => $item['date'] ?? null,
            'name' => trim((string) ($item['description'] ?? $item['name'] ?? '')),
            'type' => null,
        ])->all();

        $entries = $this->importService->normalize($rawRows);
        $entries = $this->withSourceDescription($entries, "Import via API — tahun {$year}");

        $source = "API Hari Libur Nasional — tahun {$year}";
        $this->storePreview($entries, $source);

        return view('hrd.holidays.import-preview', [
            'entries' => $entries,
            'source' => $source,
            'year' => $year,
        ]);
    }

    /**
     * Tahap 2: konfirmasi data hasil preview (Excel/API) untuk disimpan.
     * Hanya baris berstatus "new" yang diimport; sisanya dilewati.
     */
    public function importConfirm(Request $request)
    {
        $entries = session('holiday_import');

        if (! is_array($entries) || count($entries) === 0) {
            return back()->with('error', 'Tidak ada data import yang tertunda. Silakan mulai import kembali.');
        }

        $result = $this->importService->commit($entries, true);
        $counts = $this->importService->countsByStatus($entries);

        $request->session()->forget(['holiday_import', 'holiday_import_source']);

        $parts = ["Import berhasil: {$result['created']} hari libur baru ditambahkan."];

        if ($counts['exists'] > 0) {
            $parts[] = "{$counts['exists']} dilewati (tanggal sudah ada).";
        }

        if ($counts['duplicate'] > 0) {
            $parts[] = "{$counts['duplicate']} dilewati (duplikat dalam sumber).";
        }

        if ($counts['error'] > 0) {
            $parts[] = "{$counts['error']} baris gagal (format tidak valid).";
        }

        $year = collect($entries)
            ->pluck('date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->first() ?? now()->year;

        return redirect()->route('hrd.holidays.index', ['year' => $year])
            ->with('success', implode(' ', $parts));
    }

    /**
     * Batal import: hapus data preview dari session, tidak ada perubahan DB.
     */
    public function importCancel(Request $request)
    {
        $request->session()->forget(['holiday_import', 'holiday_import_source']);

        return redirect()->route('hrd.holidays.index')
            ->with('success', 'Import dibatalkan, tidak ada data yang diubah.');
    }

    private function storePreview(array $entries, string $source): void
    {
        session([
            'holiday_import' => $entries,
            'holiday_import_source' => $source,
        ]);
    }

    private function withSourceDescription(array $entries, string $description): array
    {
        return collect($entries)->map(function ($entry) use ($description) {
            if (($entry['status'] ?? '') === HolidayImportService::STATUS_NEW) {
                $entry['description'] = $description;
            }

            return $entry;
        })->all();
    }
}
