<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LangananPembayaran;
use App\Support\TenantContext;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman langganan sisi toko: status tier saat ini + tabel perbandingan +
 * riwayat pembayaran + CTA upgrade. Belum ada self-serve bayar (jalur gateway
 * ditunda) — upgrade lewat WhatsApp ke SiKasir.
 */
class LanggananController extends Controller
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function index(): Response
    {
        $toko = $this->tenant->toko();

        $tiers = collect(config('langganan.tiers'))
            ->map(fn (array $t, string $key) => [
                'key' => $key,
                'label' => $t['label'],
                'harga' => $t['harga'],
                'urutan' => $t['urutan'],
            ])
            ->sortBy('urutan')
            ->values();

        $riwayat = $toko
            ? $toko->pembayarans()
                ->latest('created_at')
                ->take(12)
                ->get()
                ->map(fn (LangananPembayaran $p) => [
                    'tier' => $p->tier,
                    'nominal' => $p->nominal,
                    'metode' => $p->metode,
                    'periode_mulai' => $p->periode_mulai->toDateString(),
                    'periode_sampai' => $p->periode_sampai->toDateString(),
                    'tanggal' => $p->created_at->translatedFormat('d M Y'),
                    'catatan' => $p->catatan,
                ])
                ->values()
            : collect();

        return Inertia::render('admin/Langganan', [
            'tier_efektif' => $toko?->tierEfektif() ?? 'gratis',
            'tier_langganan' => $toko?->tier ?? 'gratis',
            'langganan_sampai' => $toko?->langganan_sampai?->toDateString(),
            'tiers' => $tiers,
            // Peta fitur ber-gate → tier minimum yang dibutuhkan (untuk tabel).
            'fitur' => config('langganan.fitur'),
            'kontak_wa' => config('langganan.kontak_wa'),
            'riwayat' => $riwayat,
        ]);
    }
}
