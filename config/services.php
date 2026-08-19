<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // API pihak ketiga untuk sinkronisasi hari libur nasional & cuti bersama.
    // Hanya dipakai sebagai pembuat draft (is_active=false) yang direview HR.
    // Ganti endpoint lewat env HOLIDAY_API_URL tanpa mengubah kode.
    'holiday_api' => [
        'url' => env('HOLIDAY_API_URL', 'https://api-hari-libur.vercel.app/api'),
    ],

];
