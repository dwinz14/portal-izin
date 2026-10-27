<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\DTOs\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Pengajuan kehadiran yang sudah disetujui dihapus oleh atasan.
 *
 * Sengaja menerima SNAPSHOT berupa array (bukan model AttendanceRequest)
 */
class AttendanceRequestDeleted extends Notification implements ShouldQueue
{
    use Queueable;

    /** Maksimal percobaan kirim */
    public int $tries = 3;

    /** Jeda retry eksponensial jika gagal: 60s → 120s → 300s */
    public array $backoff = [60, 120, 300];

    public function __construct(public array $snapshot) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        $actor   = ucwords($this->snapshot['actor_name']);
        $tanggal = Carbon::parse($this->snapshot['date'])->isoFormat('D MMMM Y');

        return [
            'type'    => 'attendance_request_deleted',
            'title'   => 'Pengajuan Kehadiran Dihapus',
            'message' => "{$actor} menghapus pengajuan {$this->jenis()} Anda tanggal {$tanggal} yang sudah disetujui.",
            'data'    => ['attendance_request_id' => $this->snapshot['attendance_request_id']],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $tanggal = Carbon::parse($this->snapshot['date'])->isoFormat('D MMMM Y');
        $jam     = collect([$this->snapshot['start_time'], $this->snapshot['end_time']])->filter()->implode(' - ');

        $text = "PENGAJUAN KEHADIRAN DIHAPUS\n\n"
            . 'Halo ' . ucwords($notifiable->name) . ",\n"
            . 'Pengajuan kehadiran Anda yang sudah disetujui telah dihapus oleh '
            . ucwords($this->snapshot['actor_name']) . ":\n\n"
            . "Jenis: {$this->jenis()}\n"
            . "Tanggal: {$tanggal}\n"
            . ($jam !== '' ? "Jam: {$jam}\n" : '')
            . "Alasan: {$this->snapshot['alasan']}\n\n"
            . "Jika masih diperlukan, silakan ajukan kembali di SIMIKA.\n";

        return WhatsAppMessage::create($text);
    }

    private function jenis(): string
    {
        return $this->snapshot['type']
            . ($this->snapshot['update_type'] ? " ({$this->snapshot['update_type']})" : '');
    }
}
