<?php

namespace App\Notifications;

use App\Models\AttendanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AttendanceRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(private AttendanceRequest $attendanceRequest)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'attendance_request',
            'title' => 'Pengajuan Kehadiran Baru',
            'message' => "Pengajuan {$this->attendanceRequest->type_label} dari {$this->attendanceRequest->user->name} membutuhkan persetujuan Anda.",
            'data' => ['attendance_request_id' => $this->attendanceRequest->id],
        ];
    }
}
