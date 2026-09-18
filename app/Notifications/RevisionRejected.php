<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\DTOs\WhatsAppMessage;
use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class RevisionRejected extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave  $leave,
        public string $employeeName,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'revision_rejected',
            'title'   => 'Revisi Ditolak',
            'message' => "{$this->employeeName} menolak revisi tanggal cuti Anda. Pengajuan dibatalkan.",
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::create(
            "REVISI TANGGAL DITOLAK\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "" . ucwords($this->employeeName) . " menolak usulan revisi tanggal izin/cuti anda.\n\n" .
                "Pengajuan cuti dibatalkan.\n"
        );
    }
}
