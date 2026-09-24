<?php

namespace App\Services;

use App\Models\Leave;

/**
 * Kumpulan pengecekan tumpang tindih (overlap) jadwal cuti & pengganti.
 */
class LeaveOverlapChecker
{
    /**
     * Cek apakah user (sebagai pemohon) sudah punya cuti pending/approved yang tanggalnya bentrok.
     * Sengaja tidak membatasi JUMLAH pengajuan pending (user boleh punya beberapa pengajuan
     * pending sekaligus), tapi tetap mencegah 2 pengajuan dengan rentang tanggal yang tumpang tindih.
     */
    public function hasOverlapLeave(int $userId, string $start, string $end, ?int $excludeLeaveId = null): bool
    {
        return Leave::where('user_id', $userId)
            ->whereNotIn('status_final', ['rejected'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->when($excludeLeaveId, fn($q) => $q->where('id', '!=', $excludeLeaveId))
            ->exists();
    }

    /**
     * Cek apakah calon pengganti sedang mengajukan cuti sendiri di tanggal tersebut.
     */
    public function hasReplacementOnLeave(int $replacementId, string $start, string $end, ?int $excludeLeaveId = null): bool
    {
        return Leave::where('user_id', $replacementId)
            ->whereNotIn('status_final', ['rejected'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->when($excludeLeaveId, fn($q) => $q->where('id', '!=', $excludeLeaveId))
            ->exists();
    }

    /**
     * Cek apakah calon pengganti sudah ditugaskan sebagai pengganti pada cuti lain
     * yang tanggalnya bentrok.
     */
    public function hasOverlapReplacement(int $replacementId, string $start, string $end, ?int $excludeLeaveId = null): bool
    {
        return Leave::where('pengganti_id', $replacementId)
            ->whereNotIn('status_final', ['rejected'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->when($excludeLeaveId, fn($q) => $q->where('id', '!=', $excludeLeaveId))
            ->exists();
    }
}
