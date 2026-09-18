<?php

namespace App\Contracts;

use App\DTOs\WhatsAppMessage;

interface WhatsAppDriverInterface
{
    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param  string           $recipient  Nomor tujuan format internasional (628xxx)
     * @param  WhatsAppMessage  $message    Objek pesan
     * @return bool             True jika berhasil, false jika gagal
     */
    public function send(string $recipient, WhatsAppMessage $message): bool;
}
