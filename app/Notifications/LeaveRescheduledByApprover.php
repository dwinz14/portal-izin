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
 * Tanggal izin/cuti yang sudah final approved diubah oleh atasan.
 * Dikirim ke pemilik cuti ('pemohon') dan pengganti ('pengganti').
 */
class LeaveRescheduledByApprover extends Notification implements ShouldQueue
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
        $before    = $this->adjustment->before;
        $after     = $this->adjustment->after;
        $oldPeriod = $this->periodText($before);
        $newPeriod = $this->periodText($after);

        if ($this->isRequester()) {
            $title   = 'Tanggal Izin/Cuti Anda Diubah';
            $message = "{$this->adjustmentActor()} mengubah tanggal {$before['jenis_cuti']} Anda dari {$oldPeriod} menjadi {$newPeriod}."
                . (($quota = $this->quotaSentence()) ? " {$quota}" : '');
        } else {
            $title   = 'Periode Penugasan Pengganti Berubah';
            $message = "{$this->adjustmentActor()} mengubah tanggal izin/cuti {$this->adjustmentEmployee()} dari "
                . "{$oldPeriod} menjadi {$newPeriod}. Periode penugasan Anda sebagai pengganti ikut berubah.";
        }

        return [
            'type'    => 'leave_rescheduled_by_approver',
            'title'   => $title,
            'message' => $message,
            'data'    => ['adjustment_id' => $this->adjustment->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $before    = $this->adjustment->before;
        $after     = $this->adjustment->after;
        $oldPeriod = $this->periodText($before);
        $newPeriod = $this->periodText($after);
        $name      = ucwords($notifiable->name);

        if ($this->isRequester()) {
            $text = "TANGGAL IZIN/CUTI ANDA DIUBAH\n\n"
                . "Halo {$name},\n"
                . "Tanggal izin/cuti Anda telah diubah oleh {$this->adjustmentActor()}:\n\n"
                . "Jenis: {$before['jenis_cuti']}\n"
                . "Periode Sebelumnya: {$oldPeriod} ({$before['total_hari']} hari kerja)\n"
                . "Periode Baru: {$newPeriod} ({$after['total_hari']} hari kerja)\n"
                . "Alasan: {$this->adjustment->alasan}\n";

            if ($quota = $this->quotaSentence()) {
                $text .= "\n{$quota}\n";
            }
        } else {
            $text = "PERIODE PENUGASAN PENGGANTI BERUBAH\n\n"
                . "Halo {$name},\n"
                . "Tanggal izin/cuti {$this->adjustmentEmployee()} telah diubah oleh {$this->adjustmentActor()}, "
                . "sehingga periode penugasan Anda sebagai pengganti ikut berubah:\n\n"
                . "Periode Sebelumnya: {$oldPeriod}\n"
                . "Periode Baru: {$newPeriod}\n";
        }

        return WhatsAppMessage::create($text);
    }
}
