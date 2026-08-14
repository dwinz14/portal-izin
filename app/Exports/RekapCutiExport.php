<?php

namespace App\Exports;

use App\Models\Leave;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RekapCutiExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithChunkReading
{
    protected $filters;

    /**
     * $filters: array dengan keys: position_id, start_date, end_date, leave_type_id, status_final
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query builder -> FromQuery akan meng-stream hasilnya (lebih hemat memory).
     */
    public function query()
    {
        $q = Leave::query()
            ->with(['user.position', 'user.office', 'approvalHistories.approver', 'leaveType']);

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
        $lastApproval = $leave->approvalHistories()->latest()->first();

        return [
            $leave->user->nik ?? '-',
            ucwords($leave->user->name ?? '-'),
            strtoupper($leave->user->position->nama_jabatan ?? '-'),
            strtoupper($leave->user->office->nama_kantor ?? '-'),
            $leave->leaveType->name ?? '-',
            $leave->start_date,
            $leave->end_date,
            $leave->total_hari,
            strtoupper($leave->status_final ?? 'pending'),
            ucwords(optional($leave->approvals()->where('step', 2)->first()?->approver)->name ?? '-'),
            $lastApproval ? $lastApproval->created_at->format('Y-m-d H:i:s') : '-',
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
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Total Hari',
            'Status Akhir',
            'Approver Terakhir',
            'Waktu Approver',
            'Alasan',
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
