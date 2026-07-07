<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Landing page publik SiKasir-the-product (halaman marketing produk, BUKAN
 * storefront toko tertentu). Disajikan di root `/` (rute `home`), DI LUAR
 * middleware tenant — selalu tampil walau belum ada toko aktif sama sekali;
 * CTA-nya mengarah ke registrasi mandiri (/register), dan user yang sudah
 * login diarahkan komponennya ke /dashboard.
 *
 * Storefront toko kini HANYA via /toko/{slug} (lihat ResolveTenant &
 * SikasirLandingTest). Jangan kembalikan `/` ke HomeController — itu
 * membalikkan keputusan 2026-07-05.
 */
class SikasirController extends Controller
{
    public function landing(): Response
    {
        // Harga & label tier dibaca dari config (sumber tunggal) agar selalu
        // konsisten dengan halaman langganan toko & feature-gating. Copy fitur
        // per tier bersifat editorial → hidup di komponen Vue, bukan config.
        $tiers = collect(config('langganan.tiers'))
            ->map(fn (array $tier, string $key) => [
                'key' => $key,
                'label' => $tier['label'],
                'harga' => (int) $tier['harga'],
            ])
            ->values();

        return Inertia::render('Landing', [
            'tiers' => $tiers,
            'waKontak' => config('langganan.kontak_wa') ?: null,
        ]);
    }
}
