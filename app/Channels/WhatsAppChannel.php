<?php

namespace App\Channels;

use App\DTOs\WhatsAppMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! config('whatsapp.enabled', false)) {
            return;
        }

        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $recipient = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (empty($recipient)) {
            Log::debug("[WhatsApp] Dilewati: notifiable [{$notifiable->id}] tidak punya nomor WA.");
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        if (is_string($message)) {
            $message = WhatsAppMessage::create($message);
        }

        if (! $message instanceof WhatsAppMessage) {
            Log::warning("[WhatsApp] toWhatsApp() tidak mengembalikan WhatsAppMessage.");
            return;
        }

        // Rate limiting: pastikan jeda antar pengiriman
        $this->throttle();

        app('whatsapp')->driver()->send($recipient, $message);
    }

    /**
     * Throttle pengiriman menggunakan atomic lock.
     * Setiap pengiriman mengunci slot selama $delay detik.
     * Job berikutnya menunggu slot bebas sebelum kirim.
     */
    private function throttle(): void
    {
        $delay     = config('whatsapp.rate_limit.delay_between_messages', 5);
        $lockKey   = 'whatsapp_send_lock';
        $lockTtl   = $delay + 5; // TTL sedikit lebih panjang dari delay sebagai safety net

        // Tunggu sampai lock bebas, lalu ambil dan tahan selama $delay detik
        $waited = 0;
        while (! Cache::add($lockKey, 1, $lockTtl)) {
            sleep(1);
            $waited++;

            // Safety: jangan tunggu lebih dari 60 detik
            if ($waited >= 60) {
                Log::warning('[WhatsApp] Throttle timeout setelah 60 detik menunggu.');
                break;
            }
        }

        // Tahan lock selama delay agar job berikutnya menunggu
        Cache::put($lockKey, 1, $delay);
    }
}
