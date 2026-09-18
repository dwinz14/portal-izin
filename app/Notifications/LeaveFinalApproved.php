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

class LeaveFinalApproved extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave $leave,
        public ?int  $remainingBalance = null,
    ) {}

    public function via($notifiable): array
    {
        // Pengganti hanya terima WA (tidak perlu in-app notification)
        // Pemohon terima keduanya
        if ($notifiable->id === $this->leave->pengganti_id) {
            return [WhatsAppChannel::class];
        }

        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'leave_final_approved',
            'title'   => 'Cuti Disetujui Penuh',
            'message' => 'Pengajuan cuti Anda telah disetujui oleh semua pihak dan kuota cuti telah dipotong.',
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        // Pesan berbeda untuk pengganti
        if ($notifiable->id === $this->leave->pengganti_id) {
            return $this->messageForPengganti($notifiable);
        }

        return $this->messageForRequester($notifiable);
    }

    private function messageForRequester($notifiable): WhatsAppMessage
    {
        $start    = Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $end      = Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');
        $sisaCuti = $this->remainingBalance !== null
            ? "\n Sisa Kuota: {$this->remainingBalance} hari"
            : '';

        return WhatsAppMessage::create(
            "PENGAJUAN IZIN/CUTI DISETUJUI\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "Semua pihak telah menyetujui pengajuan izin/cuti Anda.\n\n" .
                "Jenis: " . ($this->leave->leaveType->name ?? '-') . "\n" .
                "{$start} s/d {$end}\n" .
                "Durasi: {$this->leave->total_hari} hari kerja" .
                "{$sisaCuti}\n\n" .
                "Lihat Detail di SIMIKA\n"
        );
    }

    private function messageForPengganti($notifiable): WhatsAppMessage
    {
        $start = Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $end   = Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');

        return WhatsAppMessage::create(
            "INFO: ANDA DITUNJUK SEBAGAI PENGGANTI\n\n" .
                "Halo " . ucwords($notifiable->name) . "*,\n" .
                "Anda ditunjuk sebagai pengganti " . ucwords($this->leave->user->name) . " yang akan cuti.\n\n" .
                "Jenis Cuti: " . ($this->leave->leaveType->name ?? '-') . "\n" .
                "{$start} s/d {$end}\n" .
                "Durasi: {$this->leave->total_hari} hari kerja\n\n" .
                "Lihat Detail di SIMIKA\n"
        );
    }
}
