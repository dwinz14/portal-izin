<?php

namespace App\Exports;

use App\Models\Leave;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapCutiExport implements
    FromQuery,
    WithMapping,
    WithHeadings,
    ShouldAutoSize,
    WithChunkReading,
    WithColumnFormatting,
    WithStyles,
    WithEvents
{
    protected $filters;

    /**
     * $filters: array dengan keys: position_id, start_date, end_date, leave_type_id, status_final
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $q = Leave::query()
            ->with([
                'user.position',
                'user.office',
                'pengganti:id,name',
                'approvalHistories.approver:id,name',
                'approvals.approver:id,name',
                'leaveType',
            ]);

        // filter position (berdasarkan user->position di leave->user)
        if (!empty($this->filters['position_id'])) {
            $positionId = $this->filters['position_id'];
            $q->whereHas('user', function ($uq) use ($positionId) {
                $uq->where('position_id', $positionId);
            });
        }
        // filter kantor (berdasarkan user->office di leave->user)
        if (!empty($this->filters['office_id'])) {
            $officeId = $this->filters['office_id'];
            $q->whereHas('user', function ($uq) use ($officeId) {
                $uq->where('office_id', $officeId);
            });
        }

        // filter leave_type
        if (!empty($this->filters['leave_type_id'])) {
            $q->where('leave_type_id', $this->filters['leave_type_id']);
        }

        // filter tanggal: ambil leave yang terjadi di range (start_date between)
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $start = $this->filters['start_date'];
            $end = $this->filters['end_date'];
            // kita pilih semua leave yang `start_date` berada antara filter range
            // dan juga mencakup leave yang overlap dengan range
            $q->where(function ($sq) use ($start, $end) {
                $sq->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($sq2) use ($start, $end) {
                        $sq2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            });
        }

        // filter status_final (optional)
        if (!empty($this->filters['status_final'])) {
            $q->where('status_final', $this->filters['status_final']);
        }

        return $q->orderBy('created_at', 'desc');
    }

    /**
     * map each Leave model to a row
     */
    public function map($leave): array
    {
        // Pakai koleksi yang sudah di-eager-load (bukan query ulang) supaya tidak N+1.
        $lastApproval = $leave->approvalHistories->sortByDesc('created_at')->first();
        $atasanStep2  = $leave->approvals->firstWhere('step', 2);

        return [
            $leave->user->nik ?? '-',
            ucwords($leave->user->name ?? '-'),
            strtoupper($leave->user->position->nama_jabatan ?? '-'),
            strtoupper($leave->user->office->nama_kantor ?? '-'),
            $leave->leaveType->name ?? '-',
            $leave->pengganti ? ucwords($leave->pengganti->name) : '-',
            Carbon::parse($leave->start_date),
            Carbon::parse($leave->end_date),
            $leave->total_hari,
            $leave->is_mendadak ? 'Ya' : 'Tidak',
            strtoupper($leave->status_final ?? 'pending'),
            ucwords(optional($atasanStep2?->approver)->name ?? '-'),
            $lastApproval ? Carbon::parse($lastApproval->created_at) : null,
            $leave->alasan ?? '-',
        ];
    }

    /**
     * Excel headings
     */
    public function headings(): array
    {
        return [
            'NIK',
            'Nama Pemohon',
            'Jabatan',
            'Kantor',
            'Jenis Cuti',
            'Pengganti',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Total Hari',
            'Mendadak?',
            'Status Akhir',
            'Atasan (Approver)',
            'Waktu Approval Terakhir',
            'Alasan',
        ];
    }

    /**
     * Format kolom tanggal sebagai tipe Date asli di Excel (bukan teks biasa),
     * supaya HRD bisa sort/filter/hitung selisih tanggal langsung di Excel
     * tanpa perlu convert format dulu.
     */
    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'M' => 'DD/MM/YYYY HH:MM',
        ];
    }

    /**
     * Header tabel dibold + diberi warna, biar jelas beda dari baris data.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D4ED8'],
                ],
            ],
        ];
    }

    /**
     * Freeze baris header + aktifkan AutoFilter, supaya HRD bisa langsung
     * filter/sort tiap kolom begitu file dibuka, tanpa setup manual.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
            },
        ];
    }

    /**
     * chunk size (untuk streaming besar)
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
