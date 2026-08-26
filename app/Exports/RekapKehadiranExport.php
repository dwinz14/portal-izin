<?php

namespace App\Exports;

use App\Models\AttendanceRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapKehadiranExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithChunkReading,
    WithTitle,
    WithStyles,
    WithEvents
{
    protected array $filters;
    protected int $rowCount = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Rekap Kehadiran';
    }

    public function query()
    {
        $q = AttendanceRequest::query()
            ->with(['user.position', 'user.office', 'approver']);

        if (!empty($this->filters['date_from'])) {
            $q->whereDate('date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $q->whereDate('date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['type'])) {
            $q->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['status'])) {
            $q->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['office_id'])) {
            $officeId = $this->filters['office_id'];
            $q->whereHas('user', fn($uq) => $uq->where('office_id', $officeId));
        }

        if (!empty($this->filters['position_id'])) {
            $positionId = $this->filters['position_id'];
            $q->whereHas('user', fn($uq) => $uq->where('position_id', $positionId));
        }

        return $q->orderBy('date', 'asc')->orderBy('created_at', 'asc');
    }

    public function map($row): array
    {
        $this->rowCount++;

        $typeLabels = AttendanceRequest::typeLabels();
        $statusLabel = match ($row->status) {
            'approved' => 'Disetujui',
            'rejected'  => 'Ditolak',
            default     => 'Menunggu',
        };

        $waktu = $row->start_time
            ? substr($row->start_time, 0, 5)
            . ($row->end_time ? ' - ' . substr($row->end_time, 0, 5) : '')
            : '-';

        $approvedOrRejectedAt = $row->approved_at ?? $row->rejected_at;

        return [
            $this->rowCount,                                               // No
            $row->user->nik ?? '-',                                        // NIK
            ucwords(strtolower($row->user->name ?? '-')),                  // Nama
            strtoupper($row->user->position->nama_jabatan ?? '-'),         // Jabatan
            strtoupper($row->user->office->nama_kantor ?? '-'),            // Kantor
            $typeLabels[$row->type] ?? $row->type,                         // Jenis
            $row->date ? $row->date->format('d/m/Y') : '-',               // Tanggal
            $waktu,                                                         // Waktu
            $row->reason,                                                  // Alasan
            ucwords(strtolower($row->approver->name ?? '-')),              // Atasan
            $statusLabel,                                                   // Status
            $approvedOrRejectedAt
                ? $approvedOrRejectedAt->format('d/m/Y H:i')
                : '-',                                                      // Tgl Aksi
            $row->approval_note ?? $row->rejection_reason ?? '-',          // Catatan
        ];
    }

    public function headings(): array
    {
        $dateRange = '';
        if (!empty($this->filters['date_from']) || !empty($this->filters['date_to'])) {
            $from = $this->filters['date_from'] ?? '...';
            $to   = $this->filters['date_to']   ?? '...';
            $dateRange = " (Periode: {$from} s/d {$to})";
        }

        // Row 1: judul laporan (akan di-merge via WithEvents)
        // Row 2: sub-info filter
        // Row 3: header kolom
        // Maatwebsite headings() = header kolom saja, judul kita inject via WithEvents

        return [
            'No',
            'NIK',
            'Nama Karyawan',
            'Jabatan',
            'Kantor',
            'Jenis Pengajuan',
            'Tanggal',
            'Waktu',
            'Alasan',
            'Atasan',
            'Status',
            'Tanggal Aksi',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Baris header (baris 1 karena tidak ada judul extra via headings)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E40AF'], // biru-800
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'M'; // kolom terakhir (13 kolom = A–M)

                // Freeze baris pertama (header tetap terlihat saat scroll)
                $sheet->freezePane('A2');

                // Set tinggi baris header
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Border seluruh data (header + data)
                $lastRow = $this->rowCount + 1; // +1 karena header di baris 1
                if ($lastRow > 1) {
                    $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['argb' => 'FFD1D5DB'],
                            ],
                        ],
                    ]);
                }

                // Warna zebra stripe untuk baris data (selang-seling)
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF8FAFC'], // abu sangat muda
                            ],
                        ]);
                    }

                    // Warna otomatis kolom Status (kolom K = index 11)
                    $statusCell = "K{$row}";
                    $statusVal  = $sheet->getCell($statusCell)->getValue();

                    if ($statusVal === 'Disetujui') {
                        $sheet->getStyle($statusCell)->applyFromArray([
                            'font' => ['color' => ['argb' => 'FF166534'], 'bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
                        ]);
                    } elseif ($statusVal === 'Ditolak') {
                        $sheet->getStyle($statusCell)->applyFromArray([
                            'font' => ['color' => ['argb' => 'FF991B1B'], 'bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                        ]);
                    } else {
                        $sheet->getStyle($statusCell)->applyFromArray([
                            'font' => ['color' => ['argb' => 'FF92400E'], 'bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
                        ]);
                    }

                    // Kolom No (A) rata tengah
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Set lebar manual untuk kolom tertentu (override autoSize)
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(14);  // NIK
                $sheet->getColumnDimension('G')->setWidth(14);  // Tanggal
                $sheet->getColumnDimension('H')->setWidth(14);  // Waktu
                $sheet->getColumnDimension('I')->setWidth(40);  // Alasan (wrap)
                $sheet->getColumnDimension('M')->setWidth(35);  // Catatan (wrap)

                // Wrap text kolom Alasan dan Catatan
                $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("M2:M{$lastRow}")->getAlignment()->setWrapText(true);

                // Keterangan filter di bawah tabel (jika ada)
                $infoRow  = $lastRow + 2;
                $filters  = $this->filters;
                $infoText = 'Diekspor pada: ' . now()->format('d/m/Y H:i:s');

                if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                    $infoText .= ' | Periode: '
                        . ($filters['date_from'] ?? '...') . ' s/d '
                        . ($filters['date_to'] ?? '...');
                }

                $sheet->setCellValue("A{$infoRow}", $infoText);
                $sheet->getStyle("A{$infoRow}")->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF6B7280']],
                ]);
                $sheet->mergeCells("A{$infoRow}:{$lastCol}{$infoRow}");
            },
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
