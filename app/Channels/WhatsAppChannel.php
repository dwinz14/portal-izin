<?php

namespace App\Channels;

use App\DTOs\WhatsAppMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Dipanggil oleh Laravel Notification system saat via() menyertakan
     * WhatsAppChannel::class sebagai salah satu channel.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        // 1. Cek master switch — jika disabled, skip tanpa error
        if (! config('whatsapp.enabled', false)) {
            return;
        }

        // 2. Pastikan notifikasi punya method toWhatsApp()
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        // 3. Ambil nomor WA penerima (memanggil routeNotificationForWhatsApp di User)
        $recipient = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (empty($recipient)) {
            Log::debug("[WhatsApp] Dilewati: notifiable [{$notifiable->id}] tidak punya nomor WA.");
            return;
        }

        // 4. Ambil pesan dari notifikasi
        $message = $notification->toWhatsApp($notifiable);

        // Dukung return berupa string langsung (bukan hanya WhatsAppMessage)
        if (is_string($message)) {
            $message = WhatsAppMessage::create($message);
        }

        if (! $message instanceof WhatsAppMessage) {
            Log::warning("[WhatsApp] toWhatsApp() tidak mengembalikan WhatsAppMessage.");
            return;
        }

        // 5. Kirim via Manager → Driver yang aktif
        app('whatsapp')->driver()->send($recipient, $message);
    }
}
