<?php

namespace App\Notifications;

use App\Models\AttendanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceRequestRejected extends Notification
{
    use Queueable;

    public function __construct(
        private AttendanceRequest $attendanceRequest,
        private string $approverName
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'attendance_request_rejected',
            'title' => 'Pengajuan Kehadiran Ditolak',
            'message' => "Pengajuan {$this->attendanceRequest->type_label} Anda ditolak oleh {$this->approverName}.",
            'data' => ['attendance_request_id' => $this->attendanceRequest->id],
        ];
    }
}
