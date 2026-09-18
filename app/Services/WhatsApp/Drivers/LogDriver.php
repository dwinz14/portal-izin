<?php

namespace App\Services\WhatsApp\Drivers;

use App\Contracts\WhatsAppDriverInterface;
use App\DTOs\WhatsAppMessage;
use Illuminate\Support\Facades\Log;

class LogDriver implements WhatsAppDriverInterface
{
    public function __construct(private array $config = []) {}

    public function send(string $recipient, WhatsAppMessage $message): bool
    {
        $channel = $this->config['channel'] ?? 'stack';

        Log::channel($channel)->info('╔══════════════════════════════════════╗');
        Log::channel($channel)->info('║     [MOCK] WHATSAPP NOTIFICATION     ║');
        Log::channel($channel)->info('╠══════════════════════════════════════╣');
        Log::channel($channel)->info("║ Penerima : {$recipient}");
        Log::channel($channel)->info('╠══════════════════════════════════════╣');
        Log::channel($channel)->info('║ Pesan:');
        Log::channel($channel)->info($message->getContent());
        Log::channel($channel)->info('╚══════════════════════════════════════╝');

        return true;
    }
}
