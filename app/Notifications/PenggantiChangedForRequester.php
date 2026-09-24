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

class PenggantiChangedForRequester extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Leave  $leave,
        public string $atasanName,
        public string $oldPenggantiName,
        public string $newPenggantiName,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'pengganti_changed',
            'title'   => 'Pengganti Cuti Diganti',
            'message' => "{$this->atasanName} mengganti pengganti anda dari "
                . "{$this->oldPenggantiName} menjadi {$this->newPenggantiName}.",
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $start = Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $end   = Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');

        return WhatsAppMessage::create(
            "PENGGANTI CUTI ANDA DIGANTI\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "Pengganti untuk izin/cuti Anda telah diganti oleh " . ucwords($this->atasanName) . ":\n\n" .
                "Periode: {$start} s/d {$end}\n" .
                "Pengganti Sebelumnya: " . ucwords($this->oldPenggantiName) . "\n" .
                "Pengganti Baru: " . ucwords($this->newPenggantiName) . "\n\n" .
                "Status persetujuan cuti Anda tidak berubah, tetap berlaku.\n"
        );
    }
}
