<?php

namespace App\Imports;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Parser file Excel (.xlsx/.xls/.csv) untuk preview import hari libur.
 *
 * User friendly: hanya membaca SHEET PERTAMA (sheet lain seperti
 * "Petunjuk" diabaikan), dan memetakan kolom secara posisional
 * (A = Tanggal, B = Nama Hari Libur, C = Tipe) sesuai template.
 *
 * Baris header dideteksi otomatis: jika salah satu dari 5 baris pertama
 * memuat sel yang mirip "tanggal/date" + "nama/name", baris itu dilewati.
 * Dengan begitu file tanpa header, dengan baris judul, maupun file yang
 * disimpan ulang dari Google Sheets / WPS Office tetap terbaca.
 *
 * Tanggal bisa berupa teks (YYYY-MM-DD), objek tanggal Excel, atau
 * serial number Excel (mis. dari kolom berformat General).
 */
class HolidayExcelImport implements ToCollection, WithMultipleSheets
{
    protected array $rows = [];

    /**
     * Hanya baca sheet pertama. Tanpa ini Laravel Excel memproses
     * seluruh sheet (termasuk "Petunjuk") dan sheet tanpa kolom
     * tanggal/nama akan merusak hasil import.
     */
    public function sheets(): array
    {
        return [$this];
    }

    public function collection(Collection $rows): void
    {
        $cells = [];

        foreach ($rows as $row) {
            $values = array_values($row->toArray());

            $hasValue = collect($values)->contains(
                fn ($value) => $value !== null && trim((string) $value) !== ''
            );

            if (! $hasValue) {
                continue;
            }

            $cells[] = array_slice($values, 0, 3);
        }

        // Lewati baris header jika terdeteksi (maks 5 baris pertama)
        $start = 0;

        foreach (array_slice($cells, 0, 5) as $index => $row) {
            if ($this->looksLikeHeader($row)) {
                $start = $index + 1;

                break;
            }
        }

        foreach (array_slice($cells, $start) as $row) {
            $this->rows[] = [
                'date' => $this->normalizeDate($row[0] ?? null),
                'name' => trim((string) ($row[1] ?? '')),
                'type' => trim((string) ($row[2] ?? '')),
            ];
        }
    }

    public function rows(): array
    {
        return $this->rows;
    }

    private function looksLikeHeader(array $row): bool
    {
        $first = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) ($row[0] ?? '')) ?? '');
        $second = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) ($row[1] ?? '')) ?? '');

        $isDateColumn = in_array($first, [
            'tanggal', 'date', 'hari', 'tanggallyyyymmdd', 'tanggalterjadi', 'harilibur',
        ], true);
        $isNameColumn = in_array($second, [
            'nama', 'name', 'namaharilibur', 'namalibur', 'keterangan', 'description',
        ], true);

        return $isDateColumn && $isNameColumn;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $string = trim((string) $value);

        if (is_numeric($string) && (float) $string > 25569 && (float) $string < 100000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $string)->format('Y-m-d');
            } catch (\Throwable) {
                // lanjut ke parsing teks biasa
            }
        }

        return $string;
    }
}
