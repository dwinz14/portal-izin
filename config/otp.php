<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Channel Pengiriman OTP
    |--------------------------------------------------------------------------
    | 'email'     → kirim via email saja
    | 'whatsapp'  → kirim via WA; jika user tidak punya nomor, fallback ke email
    | 'both'      → kirim ke keduanya; WA hanya dikirim jika user punya nomor
    */
    'delivery_channel' => env('OTP_DELIVERY_CHANNEL', 'email'),

];
