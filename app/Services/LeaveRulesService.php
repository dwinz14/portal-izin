<?php

namespace App\Services;

use App\Exceptions\LeaveRuleViolation;
use App\Helpers\LeaveHelper;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\UserLeaveBalance;

/**
 * Aturan jadwal cuti yang dipakai bersama oleh pengajuan baru dan perubahan tanggal
 * cuti yang sudah final approved, supaya validasinya selalu konsisten.
 *
 * Aturan yang terikat pada pemohon/jenis cuti saat pengajuan (masa kerja minimal,
 * gender, bukti sakit) tetap berada di jalur pengajuan, bukan di sini.
 */
class LeaveRulesService
{
    public function __construct(
        protected LeaveOverlapChecker $overlapChecker,
    ) {}

    /**
     * Pastikan rentang tanggal boleh dipakai. Urutan pengecekan & pesan error
     * identik dengan yang sebelumnya ada di LeaveController@store.
     *
     * @param  Leave|null  $existing  Cuti yang SEDANG diubah tanggalnya (sudah final approved).
     *                                null = pengajuan baru.
     * @return int  Total hari kerja pada rentang tersebut.
     *
     * @throws LeaveRuleViolation
     */
    public function assertSchedulable(
        int $employeeId,
        LeaveType $leaveType,
        string $start,
        string $end,
        ?int $penggantiId = null,
        ?Leave $existing = null,
    ): int {
        $excludeId = $existing?->id;

        $totalHari = LeaveHelper::calculateWorkingDays($start, $end);

        // Tolak rentang tanpa hari kerja sama sekali
        // (seluruhnya akhir pekan / libur nasional / cuti bersama)
        if ($totalHari <= 0) {
            throw new LeaveRuleViolation('Rentang tanggal yang dipilih tidak memiliki hari kerja (seluruhnya akhir pekan, libur nasional, atau cuti bersama).');
        }

        $this->assertQuota($employeeId, $leaveType, $totalHari, $existing);

        // Tumpang tindih dengan cuti pemohon sendiri (pending maupun approved)
        if ($this->overlapChecker->hasOverlapLeave($employeeId, $start, $end, $excludeId)) {
            throw new LeaveRuleViolation('Tanggal yang dipilih bertabrakan dengan pengajuan cuti Anda yang lain (pending atau sudah disetujui).');
        }

        // Pengganti sedang mengajukan cuti sendiri di tanggal tersebut
        if ($penggantiId && $this->overlapChecker->hasReplacementOnLeave($penggantiId, $start, $end, $excludeId)) {
            throw new LeaveRuleViolation('Pengganti yang dipilih sedang mengajukan cuti di tanggal tersebut.');
        }

        // Pengganti sudah ditugaskan di cuti lain yang bentrok
        if ($penggantiId && $this->overlapChecker->hasOverlapReplacement($penggantiId, $start, $end, $excludeId)) {
            throw new LeaveRuleViolation('Pengganti tersebut sudah ditugaskan pada cuti lain.');
        }

        // Pemohon sedang menjadi pengganti untuk cuti orang lain
        if ($this->overlapChecker->hasOverlapReplacement($employeeId, $start, $end, $excludeId)) {
            throw new LeaveRuleViolation('Pemohon sedang jadi pengganti di tanggal tersebut.');
        }

        return $totalHari;
    }

    /**
     * Cek kuota.
     *
     * Pengajuan baru: 'remaining' belum dikurangi (baru dipotong saat final approved), jadi
     * total_hari pengajuan PENDING lain (jenis cuti sama, tahun saldo yang sama) ikut dihitung
     * sebagai reservasi.
     *
     * Cuti yang diubah tanggalnya: hari lama sudah dipotong dari saldo, sehingga yang dicek
     * hanya SELISIH tambahan terhadap saldo pada tahun yang dulu dipotong (leaves.quota_year).
     */
    private function assertQuota(int $employeeId, LeaveType $leaveType, int $totalHari, ?Leave $existing): void
    {
        if ($leaveType->quota <= 0) {
            return;
        }

        if ($existing) {
            // quota_year NULL = tidak ada pemotongan saldo yang tercatat (data lama): tidak ada yang dicek.
            if ($existing->quota_year === null) {
                return;
            }

            $year   = (int) $existing->quota_year;
            $needed = $totalHari - (int) $existing->total_hari;

            // Rentang dipersingkat / sama: tidak butuh kuota tambahan.
            if ($needed <= 0) {
                return;
            }
        } else {
            $year   = now()->year;
            $needed = $totalHari;
        }

        $balance = UserLeaveBalance::where('user_id', $employeeId)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', $year)
            ->first();

        $pendingReserved = Leave::where('user_id', $employeeId)
            ->where('leave_type_id', $leaveType->id)
            ->where('status_final', 'pending')
            ->whereYear('start_date', $year)
            ->sum('total_hari');

        $available = ($balance->remaining ?? 0) - $pendingReserved;

        if (! $balance || $available < $needed) {
            throw new LeaveRuleViolation('Kuota cuti tidak mencukupi untuk jenis cuti ini (termasuk yang sudah dipakai di pengajuan lain yang masih pending).');
        }
    }
}
