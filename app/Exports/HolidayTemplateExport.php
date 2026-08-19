<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Template Excel untuk input massal hari libur nasional & cuti bersama.
 * Terdiri dari 2 sheet: "Template" (kolom + contoh + validasi dropdown)
 * dan "Petunjuk" (panduan pengisian berbahasa Indonesia).
 */
class HolidayTemplateExport implements WithMultipleSheets
{
    public function __construct(protected int $year) {}

    public function sheets(): array
    {
        return [
            new HolidayTemplateSheet($this->year),
            new HolidayGuideSheet,
        ];
    }
}
