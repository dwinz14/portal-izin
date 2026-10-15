<?php

namespace App\Services;

use App\Models\Leave;
use App\Notifications\LeaveFinalApproved;
use Illuminate\Support\Facades\DB;

class LeaveApprovalService
{
    public function __construct(
        protected LeaveBalanceService $balances,
    ) {}

    public function finalApprove(Leave $leave): void
    {
        // Status final + pemotongan saldo harus atomik: tidak boleh ada cuti approved
        // tanpa saldo terpotong (atau sebaliknya) bila salah satu langkah gagal.
        $remainingBalance = DB::transaction(function () use ($leave) {
            $leave->update(['status_final' => 'approved']);

            return $this->balances->deduct($leave);
        });

        // Notifikasi ke pemohon (database + WA)
        $leave->user->notify(new LeaveFinalApproved($leave, $remainingBalance));

        // Notifikasi ke pengganti jika ada (WA saja — pesannya berbeda)
        if ($leave->pengganti_id && $leave->pengganti) {
            $leave->pengganti->notify(new LeaveFinalApproved($leave, null));
        }
    }
}
