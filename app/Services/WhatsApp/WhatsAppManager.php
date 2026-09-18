<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppDriverInterface;
use App\Services\WhatsApp\Drivers\FonnteDriver;
use App\Services\WhatsApp\Drivers\LogDriver;
use App\Services\WhatsApp\Drivers\WhatsAppWebJsDriver;
use InvalidArgumentException;

class WhatsAppManager
{
    /** Cache driver yang sudah diinstansiasi agar tidak buat ulang. */
    private array $resolved = [];

    /**
     * Ambil driver yang aktif (atau driver tertentu jika $name diisi).
     */
    public function driver(?string $name = null): WhatsAppDriverInterface
    {
        $name ??= config('whatsapp.default', 'log');

        if (! isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->resolve($name);
        }

        return $this->resolved[$name];
    }

    /**
     * Kirim pesan langsung lewat driver aktif.
     * Shortcut untuk: app('whatsapp')->driver()->send(...)
     */
    public function send(string $recipient, \App\DTOs\WhatsAppMessage $message): bool
    {
        return $this->driver()->send($recipient, $message);
    }

    private function resolve(string $name): WhatsAppDriverInterface
    {
        $config = config("whatsapp.drivers.{$name}", []);

        return match ($name) {
            'log'              => new LogDriver($config),
            'fonnte'           => new FonnteDriver($config),
            'whatsapp_web_js'  => new WhatsAppWebJsDriver($config),
            default            => throw new InvalidArgumentException(
                "WhatsApp driver [{$name}] tidak didukung. " .
                    "Pilihan: log, fonnte, whatsapp_web_js."
            ),
        };
    }
}
