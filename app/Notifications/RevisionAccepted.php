<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RevisionAccepted extends Notification
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
            'type' => 'revision_accepted',
            'title' => 'Revisi Diterima',
            'message' => "{$this->employeeName} menyetujui revisi tanggal cuti yang Anda usulkan.",
            'data' => ['leave_id' => $this->leaveId],
        ];
    }
}
