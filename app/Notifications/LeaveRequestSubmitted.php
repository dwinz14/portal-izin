<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification
{
    use Queueable;

    protected $leaveId;
    protected $requesterName;

    public function __construct($leaveId, $requesterName)
    {
        $this->leaveId = $leaveId;
        $this->requesterName = $requesterName;
    }

    // Tentukan channel yang digunakan (database saja untuk awal)
    public function via($notifiable)
    {
        return ['database'];
    }

    // Format untuk disimpan di tabel notifications
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'leave_request',
            'title' => 'Pengajuan Cuti Baru',
            'message' => "Pengajuan cuti dari {$this->requesterName} membutuhkan persetujuan Anda.",
            'data' => ['leave_id' => $this->leaveId],
        ];
    }
}
