<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\DTOs\WhatsAppMessage;
use App\Models\LeaveAdjustment;
use App\Notifications\Concerns\DescribesLeaveAdjustment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Izin/cuti yang sudah final approved dibatalkan oleh atasan.
 * Dikirim ke pemilik cuti ('pemohon') dan pengganti ('pengganti').
 */
class LeaveCancelledByApprover extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, DescribesLeaveAdjustment;

    /** Maksimal percobaan kirim */
    public int $tries = 3;

    /** Jeda retry eksponensial jika gagal: 60s → 120s → 300s */
    public array $backoff = [60, 120, 300];

    public function __construct(
        public LeaveAdjustment $adjustment,
        public string $recipientRole = 'pemohon',
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        $before = $this->adjustment->before;
        $period = $this->periodText($before);

        if ($this->isRequester()) {
            $title   = 'Izin/Cuti Anda Dibatalkan';
            $message = "{$this->adjustmentActor()} membatalkan {$before['jenis_cuti']} Anda ({$period})."
                . (($quota = $this->quotaSentence()) ? " {$quota}" : '');
        } else {
            $title   = 'Penugasan Pengganti Dibatalkan';
            $message = "{$this->adjustmentActor()} membatalkan izin/cuti {$this->adjustmentEmployee()} ({$period}). "
                . 'Penugasan Anda sebagai pengganti pada periode ini tidak berlaku lagi.';
        }

        return [
            'type'    => 'leave_cancelled_by_approver',
            'title'   => $title,
            'message' => $message,
            'data'    => ['adjustment_id' => $this->adjustment->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $before = $this->adjustment->before;
        $period = $this->periodText($before);
        $name   = ucwords($notifiable->name);

        if ($this->isRequester()) {
            $text = "IZIN/CUTI ANDA DIBATALKAN\n\n"
                . "Halo {$name},\n"
                . "Izin/cuti Anda telah dibatalkan oleh {$this->adjustmentActor()}:\n\n"
                . "Jenis: {$before['jenis_cuti']}\n"
                . "Periode: {$period} ({$before['total_hari']} hari kerja)\n"
                . "Alasan: {$this->adjustment->alasan}\n";

            if ($quota = $this->quotaSentence()) {
                $text .= "\n{$quota}\n";
            }
        } else {
            $text = "PENUGASAN PENGGANTI DIBATALKAN\n\n"
                . "Halo {$name},\n"
                . "Izin/cuti {$this->adjustmentEmployee()} telah dibatalkan oleh {$this->adjustmentActor()}, "
                . "sehingga penugasan Anda sebagai pengganti pada periode berikut tidak berlaku lagi:\n\n"
                . "Periode: {$period}\n";
        }

        return WhatsAppMessage::create($text);
    }
}
