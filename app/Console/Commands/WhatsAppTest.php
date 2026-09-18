<?php

namespace App\Console\Commands;

use App\DTOs\WhatsAppMessage;
use Illuminate\Console\Command;

class WhatsAppTest extends Command
{
    protected $signature = 'app:whatsapp-test
                            {phone : Nomor WA tujuan (format: 08xxx atau 628xxx)}
                            {--driver= : Driver yang dipakai (default: sesuai .env)}';

    protected $description = 'Kirim pesan WhatsApp tes untuk memverifikasi koneksi provider';

    public function handle(): int
    {
        $rawPhone = $this->argument('phone');
        $driver   = $this->option('driver');

        // Normalisasi nomor (sama dengan User::routeNotificationForWhatsApp)
        $phone = preg_replace('/[^0-9]/', '', $rawPhone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        if (! str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        if (strlen($phone) < 10 || strlen($phone) > 15) {
            $this->error("Nomor WA tidak valid: {$rawPhone}");
            return Command::FAILURE;
        }

        $activeDriver = $driver ?? config('whatsapp.default', 'log');
        $isEnabled    = config('whatsapp.enabled', false);

        $this->info("╔══════════════════════════════════════╗");
        $this->info("║      WhatsApp Connection Test        ║");
        $this->info("╠══════════════════════════════════════╣");
        $this->info("║ Driver   : {$activeDriver}");
        $this->info("║ Enabled  : " . ($isEnabled ? 'YES' : 'NO (testing anyway)'));
        $this->info("║ Penerima : {$phone}");
        $this->info("╚══════════════════════════════════════╝");

        $message = WhatsAppMessage::create(
            "🔧 *TES KONEKSI WHATSAPP*\n\n" .
                "Ini adalah pesan tes dari sistem *" . config('app.name') . "*.\n\n" .
                "✅ Jika Anda menerima pesan ini, berarti konfigurasi WhatsApp notification berhasil.\n\n" .
                "🕐 Dikirim pada: " . now()->format('d/m/Y H:i:s') . "\n" .
                "🖥️ Driver: {$activeDriver}"
        );

        try {
            // Paksa pakai driver tertentu jika --driver diberikan
            $result = $driver
                ? app('whatsapp')->driver($driver)->send($phone, $message)
                : app('whatsapp')->driver()->send($phone, $message);

            if ($result) {
                $this->info("\n✅ <fg=green>Berhasil! Pesan tes terkirim ke {$phone}.</>");
                $this->line("   Cek WA Anda atau storage/logs/laravel.log jika menggunakan driver 'log'.");
                return Command::SUCCESS;
            }

            $this->error("\n❌ Gagal mengirim. Cek laravel.log untuk detail error.");
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("\n❌ Exception: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
