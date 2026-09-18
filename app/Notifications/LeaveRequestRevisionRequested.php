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

class LeaveRequestRevisionRequested extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave   $leave,
        public string  $approverName,
        public string  $revisedStart,
        public string  $revisedEnd,
        public int     $revisedDays,
        public ?string $catatan = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'leave_revision_requested',
            'title'   => 'Revisi Tanggal Cuti',
            'message' => "{$this->approverName} meminta revisi tanggal cuti Anda. Silakan tinjau.",
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $oldStart = Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $oldEnd   = Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');
        $newStart = Carbon::parse($this->revisedStart)->isoFormat('D MMMM Y');
        $newEnd   = Carbon::parse($this->revisedEnd)->isoFormat('D MMMM Y');
        $catatan  = $this->catatan ? "\n Alasan: {$this->catatan}" : '';

        return WhatsAppMessage::create(
            "PERMINTAAN REVISI TANGGAL IZIN/CUTI\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "{$this->approverName} mengusulkan perubahan tanggal izin/cuti Anda:" .
                "{$catatan}\n\n" .
                "Tanggal Semula: {$oldStart} s/d {$oldEnd} ({$this->leave->total_hari} hari)\n" .
                "Usulan Baru: {$newStart} s/d {$newEnd} ({$this->revisedDays} hari)\n\n" .
                "Harap konfirmasi TERIMA atau TOLAK usulan di SIMIKA:\n"
        );
    }
}
