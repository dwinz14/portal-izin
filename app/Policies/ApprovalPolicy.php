<?php

namespace App\Policies;

use App\Models\Approval;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApprovalPolicy
{
    /**
     * Hanya approver yang ditugaskan yang boleh bertindak,
     * dan semua step sebelumnya harus sudah approved
     */
    public function act(User $user, Approval $approval): bool
    {
        // Harus approver yang ditugaskan
        if ($approval->approver_id !== $user->id) {
            return false;
        }

        // Status harus masih pending
        if ($approval->status->value !== 'pending') {
            return false;
        }

        // Semua step sebelumnya harus sudah approved
        $previousNotApproved = $approval->leave->approvals
            ->where('step', '<', $approval->step)
            ->where(fn($a) => $a->status->value !== 'approved')
            ->count();

        return $previousNotApproved === 0;
    }
}
