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

class AttendanceRequestApproved extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

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
            'type'    => 'attendance_request_approved',
            'title'   => 'Pengajuan Kehadiran Disetujui',
            'message' => "Pengajuan {$this->attendanceRequest->type_label} Anda disetujui oleh {$this->approverName}.",
            'data'    => ['attendance_request_id' => $this->attendanceRequest->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $tanggal = Carbon::parse($this->attendanceRequest->date)->isoFormat('D MMMM Y');

        return WhatsAppMessage::create(
            " KEHADIRAN DISETUJUI\n\n" .
                "Halo " . ucwords($notifiable->name) . "*,\n" .
                "Pengajuan penyesuaian kehadiran Anda disetujui oleh {$this->approverName}.\n\n" .
                " {$this->attendanceRequest->type_label}\n" .
                "Tanggal: {$tanggal}\n\n"
        );
    }
}
