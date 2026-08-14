<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveFinalApproved extends Notification
{
    use Queueable;

    protected $leaveId;

    public function __construct($leaveId)
    {
        $this->leaveId = $leaveId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'leave_final_approved',
            'title' => 'Cuti Final Disetujui',
            'message' => 'Pengajuan cuti Anda telah disetujui secara final dan cuti telah dipotong dari kuota.',
            'data' => ['leave_id' => $this->leaveId],
        ];
    }
}
