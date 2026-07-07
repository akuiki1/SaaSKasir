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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    // Gateway pembayaran langganan (SEAM — belum dipakai). Integrasi live
    // (Snap + webhook konfirmasi) adalah follow-up; saat itu MidtransGateway
    // memanggil LanggananService::catatPembayaran(metode: 'midtrans'). Kosong
    // default supaya tak ada rahasia di repo.
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    // Pengirim WhatsApp untuk laporan tutup toko harian (fitur retensi).
    // Pola SEAM seperti Midtrans: driver 'log' default (tulis pesan ke log,
    // gratis & aman di dev/CI) — TIDAK butuh kredensial. Driver 'gateway'
    // (GatewayWhatsappSender) memakai HTTP POST bergaya gateway lokal Indonesia
    // (mis. Fonnte): isi WHATSAPP_ENDPOINT + WHATSAPP_TOKEN lalu set driver.
    // Tetap inert selama endpoint/token kosong (tidak ada panggilan HTTP live).
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'endpoint' => env('WHATSAPP_ENDPOINT'),
        'token' => env('WHATSAPP_TOKEN'),
        // Jam kirim laporan harian (24 jam, waktu server). Dijadwalkan di
        // routes/console.php; default 21:00 (menjelang tutup warung).
        'jam_kirim' => env('WHATSAPP_JAM_KIRIM', '21:00'),
    ],

];
