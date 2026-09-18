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

class RevisionAccepted extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave  $leave,
        public string $employeeName,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'revision_accepted',
            'title'   => 'Revisi Diterima',
            'message' => "{$this->employeeName} menyetujui revisi tanggal cuti yang Anda usulkan.",
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $revisionApproval = $this->leave->revisionApproval;
        $newStart = $revisionApproval
            ? Carbon::parse($revisionApproval->revised_start_date)->isoFormat('D MMMM Y')
            : Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $newEnd = $revisionApproval
            ? Carbon::parse($revisionApproval->revised_end_date)->isoFormat('D MMMM Y')
            : Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');
        $days = $revisionApproval?->revised_total_hari ?? $this->leave->total_hari;

        return WhatsAppMessage::create(
            "REVISI TANGGAL DITERIMA\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "" . ucwords($this->employeeName) . " menyetujui usulan revisi tanggal izin/cuti.\n\n" .
                "Tanggal Baru yang Disetujui:\n" .
                "{$newStart} s/d {$newEnd} ({$days} hari)\n\n" .
                "Pengajuan izin/cuti selesai diproses.\n"
        );
    }
}
