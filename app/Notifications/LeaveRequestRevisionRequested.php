<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestRevisionRequested extends Notification
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
            'type' => 'leave_revision_requested',
            'title' => 'Revisi Tanggal Cuti',
            'message' => "{$this->approverName} meminta revisi tanggal cuti Anda. Silakan tinjau.",
            'data' => ['leave_id' => $this->leaveId],
        ];
    }
}
