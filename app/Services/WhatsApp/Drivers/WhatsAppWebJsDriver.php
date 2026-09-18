<?php

namespace App\Services\WhatsApp\Drivers;

use App\Contracts\WhatsAppDriverInterface;
use App\DTOs\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebJsDriver implements WhatsAppDriverInterface
{
    public function __construct(private array $config) {}

    public function send(string $recipient, WhatsAppMessage $message): bool
    {
        try {
            // whatsapp-web.js memerlukan suffix @c.us untuk chat pribadi
            $number = str_ends_with($recipient, '@c.us')
                ? $recipient
                : "{$recipient}@c.us";

            $headers = [];
            if (! empty($this->config['api_key'])) {
                $headers['X-API-KEY'] = $this->config['api_key'];
            }

            $response = Http::timeout($this->config['timeout'] ?? 15)
                ->withHeaders($headers)
                ->post($this->config['endpoint'], [
                    'number'  => $number,
                    'message' => $message->getContent(),
                ]);

            if ($response->successful()) {
                Log::info("[WhatsApp/WebJs] Terkirim ke: {$recipient}");
                return true;
            }

            Log::error("[WhatsApp/WebJs] Gagal [{$response->status()}]: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("[WhatsApp/WebJs] Exception: " . $e->getMessage());
            return false;
        }
    }
}
