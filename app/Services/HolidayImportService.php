<?php

namespace App\Services;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Pipeline import massal hari libur (Excel, API, maupun paste manual).
 *
 * Alur: normalize() menghasilkan daftar baris berstatus
 * (new / exists / duplicate / error) untuk ditampilkan di halaman
 * verifikasi, lalu commit() menyimpan hanya baris berstatus "new"
 * dalam satu transaksi dan membersihkan cache per tahun terdampak.
 */
class HolidayImportService
{
    public const STATUS_NEW = 'new';

    public const STATUS_EXISTS = 'exists';

    public const STATUS_DUPLICATE = 'duplicate';

    public const STATUS_ERROR = 'error';

    public const TYPE_NATIONAL = 'national_holiday';

    public const TYPE_JOINT = 'joint_leave';

    /**
     * Tentukan tipe hari libur dari kolom tipe eksplisit, atau deteksi
     * otomatis dari nama ("Cuti Bersama" => joint_leave, sisanya nasional).
     */
    public function classifyType(?string $explicitType, string $name): string
    {
        $raw = strtolower(trim((string) $explicitType));

        if (in_array($raw, ['joint_leave', 'cuti_bersama', 'cuti bersama', 'cuti-bersama'], true)) {
            return self::TYPE_JOINT;
        }

        if (in_array($raw, ['national_holiday', 'nasional', 'national', 'libur nasional'], true)) {
            return self::TYPE_NATIONAL;
        }

        return str_contains(strtolower($name), 'cuti bersama')
            ? self::TYPE_JOINT
            : self::TYPE_NATIONAL;
    }

    /**
     * Normalisasi baris mentah menjadi entri berstatus.
     *
     * @param  array<int, array{date?: mixed, name?: mixed, type?: mixed}>  $rows
     * @return array<int, array{
     *     date: string|null,
     *     name: string|null,
     *     type: string|null,
     *     status: string,
     *     message: string,
     *     row: int,
     * }>
     */
    public function normalize(array $rows): array
    {
        $existing = PublicHoliday::query()
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->flip()
            ->all();

        $seen = [];
        $entries = [];

        foreach ($rows as $index => $row) {
            $lineNo = $index + 1;
            $rawDate = trim((string) ($row['date'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $rawType = trim((string) ($row['type'] ?? ''));

            if ($rawDate === '' && $name === '' && $rawType === '') {
                continue;
            }

            try {
                $date = $rawDate === '' ? null : Carbon::parse($rawDate);
            } catch (\Throwable) {
                $date = null;
            }

            if (! $date || $date->year < 2000 || $date->year > 2100) {
                $entries[] = $this->errorEntry($lineNo, "tanggal tidak valid ('{$rawDate}')");

                continue;
            }

            $dateKey = $date->format('Y-m-d');

            if ($name === '') {
                $entries[] = $this->errorEntry($lineNo, 'nama hari libur kosong');

                continue;
            }

            $type = $this->classifyType($rawType, $name);

            if (isset($seen[$dateKey])) {
                $entries[] = [
                    'date' => $dateKey,
                    'name' => $name,
                    'type' => $type,
                    'status' => self::STATUS_DUPLICATE,
                    'message' => 'Duplikat dalam data sumber — baris pertama yang dipakai',
                    'row' => $lineNo,
                ];

                continue;
            }

            $seen[$dateKey] = true;

            if (isset($existing[$dateKey])) {
                $entries[] = [
                    'date' => $dateKey,
                    'name' => $name,
                    'type' => $type,
                    'status' => self::STATUS_EXISTS,
                    'message' => 'Tanggal sudah ada di database — dilewati',
                    'row' => $lineNo,
                ];

                continue;
            }

            $entries[] = [
                'date' => $dateKey,
                'name' => $name,
                'type' => $type,
                'status' => self::STATUS_NEW,
                'message' => 'Siap diimport',
                'row' => $lineNo,
            ];
        }

        return $entries;
    }

    /**
     * Simpan hanya baris berstatus "new" dalam satu transaksi.
     * Baris exists/duplicate/error dilewati tanpa mengubah data lama.
     *
     * @return array{created: int, skipped: int}
     */
    public function commit(array $entries, bool $active = true): array
    {
        $created = 0;
        $skipped = 0;
        $affectedYears = [];

        DB::transaction(function () use ($entries, $active, &$created, &$skipped, &$affectedYears) {
            foreach ($entries as $entry) {
                if (($entry['status'] ?? '') !== self::STATUS_NEW) {
                    $skipped++;

                    continue;
                }

                try {
                    $date = Carbon::parse($entry['date'] ?? '')->format('Y-m-d');
                } catch (\Throwable) {
                    $skipped++;

                    continue;
                }

                $holiday = PublicHoliday::updateOrCreate(
                    ['date' => $date],
                    [
                        'name' => trim((string) ($entry['name'] ?? '')),
                        'type' => in_array($entry['type'] ?? '', [self::TYPE_NATIONAL, self::TYPE_JOINT], true)
                            ? $entry['type']
                            : self::TYPE_NATIONAL,
                        'is_active' => $active,
                        'description' => trim((string) ($entry['description'] ?? '')),
                    ]
                );

                $affectedYears[$holiday->year] = true;
                $created++;
            }
        });

        foreach (array_keys($affectedYears) as $year) {
            PublicHoliday::clearCacheForYear($year);
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Hitung jumlah baris per status untuk ringkasan di halaman verifikasi.
     */
    public function countsByStatus(array $entries): array
    {
        $counts = [self::STATUS_NEW => 0, self::STATUS_EXISTS => 0, self::STATUS_DUPLICATE => 0, self::STATUS_ERROR => 0];

        foreach ($entries as $entry) {
            $status = $entry['status'] ?? self::STATUS_ERROR;
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        return $counts;
    }

    private function errorEntry(int $row, string $reason): array
    {
        return [
            'date' => null,
            'name' => null,
            'type' => null,
            'status' => self::STATUS_ERROR,
            'message' => "Baris {$row}: {$reason}",
            'row' => $row,
        ];
    }
}
