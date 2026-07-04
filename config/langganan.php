<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tier langganan SiKasir
    |--------------------------------------------------------------------------
    |
    | Sumber tunggal definisi tier. `urutan` dipakai untuk membandingkan tier
    | (feature-gating: butuh urutan >= tier minimum fitur). `harga` dalam rupiah
    | per bulan — dipakai halaman langganan & default nominal command.
    |
    */

    'tiers' => [
        'gratis' => ['label' => 'Gratis', 'harga' => 0, 'urutan' => 0],
        'warung' => ['label' => 'Warung', 'harga' => 99000, 'urutan' => 1],
        'bisnis' => ['label' => 'Bisnis', 'harga' => 199000, 'urutan' => 2],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature-gating: fitur => tier minimum
    |--------------------------------------------------------------------------
    |
    | Fitur yang TIDAK terdaftar di sini dianggap terbuka untuk semua tier.
    | Pass pertama hanya mengunci Laporan Keuangan (Laba Rugi) di balik Warung+.
    |
    */

    'fitur' => [
        'laporan_keuangan' => 'warung',
    ],

    // Nomor WhatsApp SiKasir untuk CTA "Upgrade" di halaman langganan toko.
    'kontak_wa' => env('SIKASIR_WA_LANGGANAN', ''),

];
