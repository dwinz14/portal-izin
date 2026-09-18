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

class LeaveRequestApproved extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave   $leave,
        public string  $approverName,
        public ?string $catatan = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'leave_approved',
            'title'   => 'Cuti Disetujui',
            'message' => "Pengajuan cuti Anda telah disetujui oleh {$this->approverName}.",
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $start = Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $end   = Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');
        $catatan = $this->catatan ? "\n Catatan: {$this->catatan}" : '';

        return WhatsAppMessage::create(
            "PENGAJUAN IZIN/CUTI DISETUJUI\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "Pengajuan izin/cuti Anda disetujui oleh {$this->approverName}." .
                "{$catatan}\n\n" .
                "Jenis: " . ($this->leave->leaveType->name ?? '-') . "\n" .
                "{$start} s/d {$end}\n\n" .
                "Pengajuan diteruskan ke tahap persetujuan berikutnya.\n"
        );
    }
}
