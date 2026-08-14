<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestApproved extends Notification
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
            'type' => 'leave_approved',
            'title' => 'Cuti Disetujui',
            'message' => "Pengajuan cuti Anda telah disetujui oleh {$this->approverName}.",
            'data' => ['leave_id' => $this->leaveId],
        ];
    }
}
