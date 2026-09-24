<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\DTOs\WhatsAppMessage;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class AttendanceRequestRejected extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Maksimal percobaan kirim */
    public int $tries     = 3;

    /** Jeda retry eksponensial jika gagal: 60s → 120s → 300s */
    public array $backoff = [60, 120, 300];

    public function __construct(
        public AttendanceRequest $attendanceRequest,
        public string            $approverName,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'attendance_request_rejected',
            'title'   => 'Pengajuan Kehadiran Ditolak',
            'message' => "Pengajuan {$this->attendanceRequest->type_label} Anda ditolak oleh {$this->approverName}.",
            'data'    => ['attendance_request_id' => $this->attendanceRequest->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $tanggal = Carbon::parse($this->attendanceRequest->date)->isoFormat('D MMMM Y');
        $alasan  = $this->attendanceRequest->rejection_reason
            ? "\n Alasan: {$this->attendanceRequest->rejection_reason}"
            : '';

        return WhatsAppMessage::create(
            " KEHADIRAN DITOLAK\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "Pengajuan penyesuaian kehadiran Anda ditolak oleh {$this->approverName}.\n\n" .
                " {$this->attendanceRequest->type_label}\n" .
                "Tanggal: {$tanggal}" .
                "{$alasan}\n\n"
        );
    }
}
