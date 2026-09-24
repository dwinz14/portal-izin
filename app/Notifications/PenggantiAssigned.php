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

class PenggantiAssigned extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Maksimal percobaan kirim */
    public int $tries     = 3;

    /** Jeda retry eksponensial jika gagal: 60s → 120s → 300s */
    public array $backoff = [60, 120, 300];

    public function __construct(
        public Leave  $leave,
        public string $atasanName,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'pengganti_assigned',
            'title'   => 'Ditunjuk Sebagai Pengganti',
            'message' => "Anda ditunjuk sebagai pengganti untuk cuti {$this->leave->user->name} "
                . "oleh {$this->atasanName}.",
            'data'    => ['leave_id' => $this->leave->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $start = Carbon::parse($this->leave->start_date)->isoFormat('D MMMM Y');
        $end   = Carbon::parse($this->leave->end_date)->isoFormat('D MMMM Y');

        return WhatsAppMessage::create(
            "PENUNJUKAN PENGGANTI CUTI\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "Anda ditunjuk sebagai pengganti untuk izin/cuti berikut:\n\n" .
                "Pemohon: " . ucwords($this->leave->user->name) . "\n" .
                "Jenis Cuti: " . ($this->leave->leaveType->name ?? '-') . "\n" .
                "Periode: {$start} s/d {$end}\n\n" .
                "Penunjukan ini dilakukan oleh " . ucwords($this->atasanName) .
                " menggantikan pengganti sebelumnya. Pengajuan ini sudah final approved, " .
                "Detail bisa dilihat di SIMIKA.\n"
        );
    }
}
