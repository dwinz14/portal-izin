<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master Switch Notifikasi WhatsApp
    |--------------------------------------------------------------------------
    | Set false untuk mematikan semua pengiriman WA secara instan tanpa
    | perlu mengubah satu baris pun di kode notifikasi.
    */
    'enabled' => env('WHATSAPP_NOTIFICATION_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Driver Default
    |--------------------------------------------------------------------------
    | Driver yang aktif digunakan: 'log', 'fonnte', 'whatsapp_web_js'
    | Gunakan 'log' untuk development — pesan ditulis ke laravel.log.
    */
    'default' => env('WHATSAPP_DEFAULT_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Per Driver
    |--------------------------------------------------------------------------
    */
    'drivers' => [

        // Driver lokal — aman untuk dev & testing, tidak kirim WA sungguhan
        'log' => [
            'channel' => env('WHATSAPP_LOG_CHANNEL', 'stack'),
        ],

        // Fonnte — layanan cloud berbayar (https://fonnte.com)
        'fonnte' => [
            'endpoint' => env('FONNTE_ENDPOINT', 'https://api.fonnte.com/send'),
            'token'    => env('FONNTE_TOKEN', ''),
            'timeout'  => (int) env('FONNTE_TIMEOUT', 15),
        ],

        // whatsapp-web.js / Baileys — self-hosted microservice (Node.js)
        'whatsapp_web_js' => [
            'endpoint' => env('WA_WEBJS_ENDPOINT', 'http://127.0.0.1:3000/api/send-message'),
            'api_key'  => env('WA_WEBJS_API_KEY', ''),
            'timeout'  => (int) env('WA_WEBJS_TIMEOUT', 15),
        ],
    ],

    // rate limiter antar chat wa
    'rate_limit' => [
        'delay_between_messages' => (int) env('WA_DELAY_BETWEEN_MESSAGES', 3),
        'retry_after'            => (int) env('WA_RETRY_AFTER', 90),
        'max_attempts'           => (int) env('WA_MAX_ATTEMPTS', 3),
    ],

];
