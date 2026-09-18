<?php

namespace App\Services\WhatsApp\Drivers;

use App\Contracts\WhatsAppDriverInterface;
use App\DTOs\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteDriver implements WhatsAppDriverInterface
{
    public function __construct(private array $config) {}

    public function send(string $recipient, WhatsAppMessage $message): bool
    {
        try {
            $response = Http::timeout($this->config['timeout'] ?? 15)
                ->withHeaders([
                    'Authorization' => $this->config['token'],
                ])
                ->post($this->config['endpoint'], [
                    'target'      => $recipient,
                    'message'     => $message->getContent(),
                    'countryCode' => '62',
                ]);

            if ($response->successful()) {
                Log::info("[WhatsApp/Fonnte] Terkirim ke: {$recipient}");
                return true;
            }

            Log::error("[WhatsApp/Fonnte] Gagal [{$response->status()}]: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("[WhatsApp/Fonnte] Exception: " . $e->getMessage());
            return false;
        }
    }
}
