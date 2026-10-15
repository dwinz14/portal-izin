<?php

namespace App\Http\Controllers;

use App\Exceptions\LeaveRuleViolation;
use App\Http\Requests\CancelApprovedLeaveRequest;
use App\Http\Requests\RescheduleApprovedLeaveRequest;
use App\Models\Leave;
use App\Models\LeaveAdjustment;
use App\Models\User;
use App\Notifications\LeaveCancelledByApprover;
use App\Notifications\LeaveRescheduledByApprover;
use App\Services\LeaveAdjustmentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Pembatalan & perubahan tanggal izin/cuti yang sudah final approved, oleh atasan.
 */
class LeaveAdjustmentController extends Controller
{
    public function __construct(
        protected LeaveAdjustmentService $adjustments,
    ) {}

    /**
     * Batalkan (hapus) izin/cuti yang sudah final approved.
     */
    public function cancel(CancelApprovedLeaveRequest $request, Leave $leave): RedirectResponse
    {
        try {
            $adjustment = $this->adjustments->cancel($leave, $request->user(), $request->validated('alasan'));
        } catch (LeaveRuleViolation $e) {
            return back()->withErrors(['msg' => $e->getMessage()])->withInput();
        } catch (ModelNotFoundException) {
            return $this->alreadyGone();
        }

        $this->notifyParties($adjustment, LeaveCancelledByApprover::class);

        $employee = ucwords($adjustment->snapshot['employee']['name'] ?? '-');

        return redirect()->route('cuti.replacement.index')
            ->with('success', "{$adjustment->before['jenis_cuti']} milik {$employee} berhasil dibatalkan.");
    }

    /**
     * Ubah tanggal izin/cuti yang sudah final approved.
     */
    public function reschedule(RescheduleApprovedLeaveRequest $request, Leave $leave): RedirectResponse
    {
        try {
            $adjustment = $this->adjustments->reschedule(
                $leave,
                $request->user(),
                $request->validated('start_date'),
                $request->validated('end_date'),
                $request->validated('alasan'),
            );
        } catch (LeaveRuleViolation $e) {
            return back()->withErrors(['msg' => $e->getMessage()])->withInput();
        } catch (ModelNotFoundException) {
            return $this->alreadyGone();
        }

        $this->notifyParties($adjustment, LeaveRescheduledByApprover::class);

        $employee = ucwords($adjustment->snapshot['employee']['name'] ?? '-');
        $old      = $this->range($adjustment->before);
        $new      = $this->range($adjustment->after);

        return redirect()->route('cuti.replacement.index')
            ->with('success', "Tanggal {$adjustment->before['jenis_cuti']} milik {$employee} berhasil diubah dari {$old} menjadi {$new}.");
    }

    /**
     * Notifikasi ke pemilik cuti & pengganti (jika ada), dikirim SETELAH transaksi selesai.
     * Kegagalan kirim tidak boleh membatalkan / mengacaukan hasil yang sudah tersimpan.
     *
     * @param  class-string  $notification
     */
    private function notifyParties(LeaveAdjustment $adjustment, string $notification): void
    {
        $recipients = [
            'pemohon'   => $adjustment->employee_id,
            'pengganti' => $adjustment->before['pengganti_id'] ?? null,
        ];

        foreach ($recipients as $role => $userId) {
            if (! $userId) {
                continue;
            }

            try {
                User::find($userId)?->notify(new $notification($adjustment, $role));
            } catch (\Throwable $e) {
                Log::error('[LeaveAdjustment] Gagal mengirim notifikasi.', [
                    'adjustment_id' => $adjustment->id,
                    'recipient'     => $userId,
                    'role'          => $role,
                    'error'         => $e->getMessage(),
                ]);
            }
        }
    }

    private function alreadyGone(): RedirectResponse
    {
        return redirect()->route('cuti.replacement.index')
            ->with('error', 'Izin/cuti tidak ditemukan, kemungkinan sudah dibatalkan.');
    }

    private function range(array $state): string
    {
        return Carbon::parse($state['start_date'])->format('d/m/Y') . ' s/d ' . Carbon::parse($state['end_date'])->format('d/m/Y');
    }
}
