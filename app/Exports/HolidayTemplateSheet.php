<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HolidayTemplateSheet implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    protected const MAX_ROWS = 1000;

    public function __construct(protected int $year) {}

    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        return ['Tanggal', 'Nama Hari Libur', 'Tipe'];
    }

    public function array(): array
    {
        return [
            ["{$this->year}-01-01", "Tahun Baru {$this->year} Masehi", 'nasional'],
            ["{$this->year}-03-20", "Cuti Bersama Hari Raya Idul Fitri {$this->year}", 'cuti_bersama'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:C1')->getFont()->setBold(true);
                $sheet->getStyle('A1:C1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDBEAFE');
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:C'.self::MAX_ROWS);

                $lastRow = self::MAX_ROWS;

                // Dropdown tipe pada kolom C
                $typeValidation = $sheet->getCell('C2')->getDataValidation();
                $typeValidation->setType(DataValidation::TYPE_LIST);
                $typeValidation->setFormula1('"nasional,cuti_bersama"');
                $typeValidation->setAllowBlank(true);
                $typeValidation->setShowDropDown(true);

                // Validasi format tanggal pada kolom A
                $dateValidation = $sheet->getCell('A2')->getDataValidation();
                $dateValidation->setType(DataValidation::TYPE_DATE);
                $dateValidation->setOperator(DataValidation::OPERATOR_BETWEEN);
                $dateValidation->setFormula1(sprintf('DATE(%d,1,1)', $this->year));
                $dateValidation->setFormula2(sprintf('DATE(%d,12,31)', $this->year));
                $dateValidation->setAllowBlank(true);

                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->setDataValidation("C{$row}", $typeValidation);
                    $sheet->setDataValidation("A{$row}", $dateValidation);
                }
            },
        ];
    }
}
