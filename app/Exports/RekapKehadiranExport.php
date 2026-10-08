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

    // Kolom terakhir — sesuaikan jika jumlah kolom berubah
    // A-O = 15 kolom
    protected string $lastCol = 'O';

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

        if (! empty($this->filters['date_from'])) {
            $q->whereDate('date', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $q->whereDate('date', '<=', $this->filters['date_to']);
        }
        if (! empty($this->filters['type'])) {
            $q->where('type', $this->filters['type']);
        }
        if (! empty($this->filters['status'])) {
            $q->where('status', $this->filters['status']);
        }
        if (! empty($this->filters['office_id'])) {
            $officeId = $this->filters['office_id'];
            $q->whereHas('user', fn($uq) => $uq->where('office_id', $officeId));
        }
        if (! empty($this->filters['position_id'])) {
            $positionId = $this->filters['position_id'];
            $q->whereHas('user', fn($uq) => $uq->where('position_id', $positionId));
        }

        return $q->orderBy('date', 'asc')->orderBy('created_at', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',               // A
            'NIK',              // B
            'Nama Karyawan',    // C
            'Jabatan',          // D
            'Kantor',           // E
            'Jenis Pengajuan',  // F
            'Detail Update',    // G — update_type khusus update_attendance
            'Tanggal',          // H
            'Jam Check-in',     // I
            'Jam Check-out',    // J
            'Alasan',           // K
            'Atasan',           // L
            'Status',           // M
            'Tanggal Aksi',     // N
            'Catatan',          // O — update lastCol jika ini ditambahkan
        ];
    }

    public function map($row): array
    {
        $this->rowCount++;

        $typeLabels  = AttendanceRequest::typeLabels();
        $updateLabels = AttendanceRequest::updateTypeLabels();

        $statusLabel = match ($row->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => 'Menunggu',
        };

        // Detail update — hanya untuk update_attendance
        $detailUpdate = '-';
        if ($row->type === AttendanceRequest::TYPE_UPDATE_ATTENDANCE && $row->update_type) {
            $detailUpdate = $updateLabels[$row->update_type] ?? $row->update_type;
        }

        // Jam check-in dan check-out dipisah
        $jamCheckin  = '-';
        $jamCheckout = '-';

        if ($row->type === AttendanceRequest::TYPE_UPDATE_ATTENDANCE) {
            // Untuk update_attendance, tampilkan sesuai update_type
            if ($row->update_type !== AttendanceRequest::UPDATE_TYPE_CHECKOUT_ONLY) {
                $jamCheckin = $row->start_time ? substr($row->start_time, 0, 5) : '-';
            }
            if (in_array($row->update_type, [
                AttendanceRequest::UPDATE_TYPE_BOTH,
                AttendanceRequest::UPDATE_TYPE_CHECKOUT_ONLY,
            ])) {
                $jamCheckout = $row->end_time ? substr($row->end_time, 0, 5) : '-';
            }
        } else {
            // Untuk type lain: start_time = jam mulai, end_time = jam selesai
            $jamCheckin  = $row->start_time ? substr($row->start_time, 0, 5) : '-';
            $jamCheckout = $row->end_time   ? substr($row->end_time, 0, 5)   : '-';
        }

        $approvedOrRejectedAt = $row->approved_at ?? $row->rejected_at;

        return [
            $this->rowCount,                                             // A — No
            $row->user->nik ?? '-',                                      // B — NIK
            ucwords(strtolower($row->user->name ?? '-')),                // C — Nama
            strtoupper($row->user->position->nama_jabatan ?? '-'),       // D — Jabatan
            strtoupper($row->user->office->nama_kantor ?? '-'),          // E — Kantor
            $typeLabels[$row->type] ?? $row->type,                       // F — Jenis
            $detailUpdate,                                               // G — Detail Update
            $row->date ? $row->date->format('d/m/Y') : '-',             // H — Tanggal
            $jamCheckin,                                                 // I — Jam Check-in
            $jamCheckout,                                                // J — Jam Check-out
            $row->reason,                                                // K — Alasan
            ucwords(strtolower($row->approver->name ?? '-')),            // L — Atasan
            $statusLabel,                                                // M — Status
            $approvedOrRejectedAt
                ? $approvedOrRejectedAt->format('d/m/Y H:i')
                : '-',                                                   // N — Tanggal Aksi
            $row->approval_note ?? $row->rejection_reason ?? '-',        // O — Catatan
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E40AF'],
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
                $sheet   = $event->sheet->getDelegate();
                $lastCol = 'P'; // 16 kolom = A–P
                $lastRow = $this->rowCount + 1;

                // Freeze header
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Border seluruh tabel
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

                for ($row = 2; $row <= $lastRow; $row++) {
                    // Zebra stripe
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF8FAFC'],
                            ],
                        ]);
                    }

                    // Kolom No rata tengah
                    $sheet->getStyle("A{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Kolom Jam Check-in dan Check-out rata tengah
                    $sheet->getStyle("I{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("J{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Warna kolom Status (N)
                    $statusCell = "N{$row}";
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
                }

                // Lebar kolom manual
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(15);  // NIK
                $sheet->getColumnDimension('G')->setWidth(20);  // Detail Update
                $sheet->getColumnDimension('H')->setWidth(13);  // Tanggal
                $sheet->getColumnDimension('I')->setWidth(13);  // Jam Check-in
                $sheet->getColumnDimension('J')->setWidth(13);  // Jam Check-out
                $sheet->getColumnDimension('K')->setWidth(40);  // Alasan
                $sheet->getColumnDimension('P')->setWidth(35);  // Catatan

                // Wrap text kolom Alasan dan Catatan
                $sheet->getStyle("K2:K{$lastRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("P2:P{$lastRow}")->getAlignment()->setWrapText(true);

                // Info ekspor di bawah tabel
                $infoRow  = $lastRow + 2;
                $filters  = $this->filters;
                $infoText = 'Diekspor pada: ' . now()->format('d/m/Y H:i:s');

                if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
                    $infoText .= ' | Periode: '
                        . ($filters['date_from'] ?? '...') . ' s/d '
                        . ($filters['date_to'] ?? '...');
                }

                $sheet->setCellValue("A{$infoRow}", $infoText);
                $sheet->getStyle("A{$infoRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF6B7280']],
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
