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
 * 2. Tanpa slug (rute akar `/`, `pesan`, `lacak-pesanan`) — fallback ke
 *    satu-satunya toko aktif. Berlaku selama masih fase satu-instance-satu-
 *    klien; begitu toko kedua onboard di infra bersama, rute akar HARUS
 *    diarahkan ke halaman marketing (bukan storefront), dan storefront wajib
 *    selalu lewat slug/subdomain — lihat rencana Fase 2 di roadmap platform.
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
