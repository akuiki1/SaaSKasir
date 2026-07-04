<?php

namespace App\Http\Middleware;

use App\Support\Langganan;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate fitur berbayar. Dipakai sebagai `fitur:{nama_fitur}` pada rute admin.
 * Menegakkan paywall di sisi server (menu yang bergembok di frontend hanya
 * penanda UX — pertahanan sebenarnya di sini).
 *
 * Kalau tier efektif toko tak mencukupi → alihkan ke halaman langganan
 * (bukan 403 mentah) agar pemilik toko diarahkan ke jalur upgrade.
 */
class PastikanFitur
{
    public function handle(Request $request, Closure $next, string $fitur): Response
    {
        $toko = app(TenantContext::class)->toko();
        $tier = $toko?->tierEfektif() ?? 'gratis';

        if (! Langganan::bolehAkses($fitur, $tier)) {
            return redirect()
                ->route('admin.langganan')
                ->with('error', 'Fitur ini memerlukan langganan berbayar. Upgrade untuk membukanya.');
        }

        return $next($request);
    }
}
