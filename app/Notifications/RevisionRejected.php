<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RevisionRejected extends Notification
{
    use Queueable;

    protected $leaveId;
    protected $employeeName;

    public function __construct($leaveId, $employeeName)
    {
        $this->leaveId = $leaveId;
        $this->employeeName = $employeeName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'revision_rejected',
            'title' => 'Revisi Ditolak',
            'message' => "{$this->employeeName} menolak revisi tanggal cuti Anda. Pengajuan dibatalkan.",
            'data' => ['leave_id' => $this->leaveId],
        ];
    }
}
