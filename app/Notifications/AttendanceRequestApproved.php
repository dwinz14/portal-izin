<?php

namespace App\Notifications;

use App\Models\AttendanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceRequestApproved extends Notification
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
            'type' => 'attendance_request_approved',
            'title' => 'Pengajuan Kehadiran Disetujui',
            'message' => "Pengajuan {$this->attendanceRequest->type_label} Anda disetujui oleh {$this->approverName}.",
            'data' => ['attendance_request_id' => $this->attendanceRequest->id],
        ];
    }
}
