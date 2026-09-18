<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\DTOs\WhatsAppMessage;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class LeaveRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Leave $leave) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'leave_request',
            'title'   => 'Pengajuan Cuti Baru',
            'message' => "Pengajuan cuti dari {$this->leave->user->name} membutuhkan persetujuan Anda.",
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $start = Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $end   = Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');

        return WhatsAppMessage::create(
            "PENGAJUAN IZIN/CUTI BARU\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "Ada pengajuan izin/cuti yang membutuhkan persetujuan Anda:\n\n" .
                "Pemohon: " . ucwords($this->leave->user->name) . "\n" .
                "Jenis Cuti: " . ($this->leave->leaveType->name ?? '-') . "\n" .
                "Periode: {$start} s/d {$end}\n" .
                "Durasi: {$this->leave->total_hari} hari kerja\n" .
                "Alasan: \"{$this->leave->alasan}\"\n\n" .
                "Tinjau dan setujui di SIMIKA\n"
        );
    }
}
