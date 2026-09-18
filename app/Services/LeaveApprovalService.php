<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\UserLeaveBalance;
use App\Notifications\LeaveFinalApproved;
use Illuminate\Support\Facades\DB;

class LeaveApprovalService
{
    public function finalApprove(Leave $leave): void
    {
        $leave->update(['status_final' => 'approved']);

        $remainingBalance = null;

        $leaveType = $leave->leaveType;
        if ($leaveType->quota > 0) {
            $balance = UserLeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if ($balance) {
                // Hitung sisa kuota SETELAH pemotongan (untuk ditampilkan di WA)
                $remainingBalance = max(0, $balance->remaining - $leave->total_hari);

                $balance->update([
                    'used'      => DB::raw("used + {$leave->total_hari}"),
                    'remaining' => DB::raw("remaining - {$leave->total_hari}"),
                ]);
            } else {
                $remainingBalance = max(0, $leaveType->quota - $leave->total_hari);

                UserLeaveBalance::create([
                    'user_id'       => $leave->user_id,
                    'leave_type_id' => $leave->leave_type_id,
                    'year'          => now()->year,
                    'total_quota'   => $leaveType->quota,
                    'used'          => $leave->total_hari,
                    'remaining'     => $remainingBalance,
                ]);
            }
        }

        // Notifikasi ke pemohon (database + WA)
        $leave->user->notify(new LeaveFinalApproved($leave, $remainingBalance));

        // Notifikasi ke pengganti jika ada (WA saja — pesannya berbeda)
        if ($leave->pengganti_id && $leave->pengganti) {
            $leave->pengganti->notify(new LeaveFinalApproved($leave, null));
        }
    }
}
