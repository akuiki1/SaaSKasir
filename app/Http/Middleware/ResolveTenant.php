<?php

namespace App\Http\Middleware;

use App\Models\Toko;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolusi toko untuk rute PUBLIK/TAMU (storefront, pesan online) — rute yang
 * tidak punya user login untuk dijadikan sumber toko aktif (beda dari rute
 * admin/kasir yang otomatis ter-scope lewat id_toko user via TenantContext).
 *
 * Sumber toko (dalam urutan ini):
 * 1. Parameter rute {tokoSlug} — dipakai rute `toko/{tokoSlug}/...` (siap
 *    multi-toko: tiap toko dapat storefront sendiri lewat slug-nya).
 * 2. Tanpa slug (rute `pesan`, `lacak-pesanan`) — fallback ke satu-satunya toko
 *    aktif. Berlaku selama masih fase satu-instance-satu-klien. Rute akar `/`
 *    TIDAK lagi lewat sini: sudah jadi landing produk SiKasir (di luar middleware
 *    tenant); storefront toko kini wajib lewat slug/subdomain — langkah Fase 2
 *    di roadmap platform. Begitu toko kedua onboard, alur `pesan`/`lacak` tanpa
 *    slug pun harus dipindah ke jalur ber-slug.
 *
 * Toko yang tidak ditemukan atau berstatus nonaktif → 404 (ModelNotFoundException
 * bawaan Laravel), bukan diam-diam fallback ke toko lain.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tokoSlug');

        $query = Toko::query()->where('status', 'aktif');

        $toko = $slug
            ? $query->where('slug', $slug)->firstOrFail()
            : $query->firstOrFail();

        app(TenantContext::class)->setToko($toko);

        return $next($request);
    }
}
