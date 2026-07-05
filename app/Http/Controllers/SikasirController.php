<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Landing page publik SiKasir-the-product (halaman marketing produk, BUKAN
 * storefront toko tertentu). Berdiri sendiri di /sikasir, DI LUAR middleware
 * tenant — tidak ter-scope ke toko manapun; CTA-nya mengarah ke registrasi
 * mandiri (/register).
 *
 * Root `/` sengaja TETAP storefront toko default (lihat ResolveTenant, fase
 * satu-instance-satu-klien); begitu instance jadi multi-tenant, `/` bisa
 * dipromosikan ke halaman ini tanpa menulis ulang komponennya.
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
