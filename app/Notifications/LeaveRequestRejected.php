<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestRejected extends Notification
{
    use Queueable;

    protected $leaveId;
    protected $approverName;

    public function __construct($leaveId, $approverName)
    {
        $this->leaveId = $leaveId;
        $this->approverName = $approverName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'leave_rejected',
            'title' => 'Cuti Ditolak',
            'message' => "Pengajuan cuti Anda telah ditolak oleh {$this->approverName}.",
            'data' => ['leave_id' => $this->leaveId],
        ];
    }
}
