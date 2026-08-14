<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
}
