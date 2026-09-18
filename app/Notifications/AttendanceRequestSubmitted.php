<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\DTOs\WhatsAppMessage;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class AttendanceRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public AttendanceRequest $attendanceRequest) {}

    public function via($notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'attendance_request',
            'title'   => 'Pengajuan Kehadiran Baru',
            'message' => "Pengajuan {$this->attendanceRequest->type_label} dari {$this->attendanceRequest->user->name} membutuhkan persetujuan Anda.",
            'data'    => ['attendance_request_id' => $this->attendanceRequest->id],
        ];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        $tanggal  = Carbon::parse($this->attendanceRequest->date)->isoFormat('D MMMM Y');
        $jamMulai = $this->attendanceRequest->start_time
            ? Carbon::parse($this->attendanceRequest->start_time)->format('H:i')
            : '-';
        $jamSelesai = $this->attendanceRequest->end_time
            ? Carbon::parse($this->attendanceRequest->end_time)->format('H:i')
            : '-';

        return WhatsAppMessage::create(
            " PENGAJUAN KEHADIRAN BARU\n\n" .
                "Halo " . ucwords($notifiable->name) . ",\n" .
                "Ada pengajuan penyesuaian kehadiran dari staf Anda:\n\n" .
                "Pemohon: " . ucwords($this->attendanceRequest->user->name) . "\n" .
                "Jenis: {$this->attendanceRequest->type_label}\n" .
                "Tanggal: {$tanggal}\n" .
                "Waktu: {$jamMulai} - {$jamSelesai}\n" .
                "Alasan: \"{$this->attendanceRequest->reason}\"\n\n" .
                "Setujui atau tolak di SIMIKA:\n"
        );
    }
}
