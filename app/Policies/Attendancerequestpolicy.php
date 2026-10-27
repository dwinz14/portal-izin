<?php

namespace App\Policies;

use App\Models\AttendanceRequest;
use App\Models\User;

class AttendanceRequestPolicy
{
    /**
     * Menghapus pengajuan kehadiran yang SUDAH final approved.
     *
     * Hanya atasan yang menyetujui pengajuan itu (approver_id). Kehadiran hanya punya
     * satu approver, jadi status 'approved' sudah berarti approval final.
     * Sengaja tanpa batas tanggal: boleh juga untuk tanggal yang sudah lewat (koreksi data).
     *
     * HARUS selaras dengan AttendanceRequest::scopeDeletableBy().
     */
    public function deleteApproved(User $user, AttendanceRequest $attendanceRequest): bool
    {
        return $attendanceRequest->status === AttendanceRequest::STATUS_APPROVED
            && (int) $attendanceRequest->approver_id === (int) $user->id;
    }
}
