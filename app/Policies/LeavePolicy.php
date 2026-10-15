<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;
use App\Models\Approval;


class LeavePolicy
{
    /**
     * User boleh melihat daftar cuti miliknya sendiri
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * User boleh melihat detail cuti miliknya saja
     */
    public function view(User $user, Leave $leave): bool
    {
        return $user->id === $leave->user_id;
    }

    /**
     * User boleh membuat pengajuan cuti
     * (validasi lebih detail ada di StoreLeaveRequest)
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * User hanya boleh hapus cuti miliknya yang masih pending
     */
    public function delete(User $user, Leave $leave): bool
    {
        return $user->id === $leave->user_id
            && $leave->status_final === 'pending';
    }

    /**
     * User hanya boleh cetak cuti miliknya yang sudah approved
     */
    public function print(User $user, Leave $leave): bool
    {
        return $user->id === $leave->user_id
            && $leave->status_final === 'approved';
    }

    /**
     * User boleh merespons revisi untuk cuti miliknya
     */
    public function respondRevision(User $user, Leave $leave): bool
    {
        return $user->id === $leave->user_id
            && $leave->is_revision_pending === true;
    }

    /**
     * Hanya atasan (approver step-2) yang final approve cuti ini yang boleh mengganti user pengganti
     */
    public function changePengganti(User $user, Leave $leave): bool
    {
        if ($leave->status_final !== 'approved' || !$leave->pengganti_id) {
            return false;
        }

        return Approval::where('leave_id', $leave->id)
            ->where('step', 2)
            ->where('approver_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Membatalkan / mengubah tanggal cuti yang SUDAH final approved.
     * Sengaja tanpa batas tanggal: boleh juga setelah periode cuti berakhir (koreksi data).
     */
    public function adjustApproved(User $user, Leave $leave): bool
    {
        if ($leave->status_final !== 'approved') {
            return false;
        }

        $finalApproval = Approval::where('leave_id', $leave->id)
            ->orderByDesc('step')
            ->first();

        if (! $finalApproval) {
            return $this->roleOf($user) === 'hrd'
                && $leave->user
                && $this->roleOf($leave->user) === 'direksi';
        }

        return $finalApproval->status === 'approved'
            && (int) $finalApproval->approver_id === (int) $user->id;
    }

    /** Role sebagai string, baik atribut biasa maupun BackedEnum. */
    private function roleOf(User $user): ?string
    {
        return $user->role instanceof \BackedEnum ? $user->role->value : $user->role;
    }
}
