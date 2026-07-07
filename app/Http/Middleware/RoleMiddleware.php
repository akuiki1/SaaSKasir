<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Admin adalah superset yang disengaja atas peran TOKO: pemilik toko
        // (termasuk hasil registrasi mandiri solo-operator) boleh akses POS
        // kasir penuh tanpa perlu akun kedua. Superset ini TIDAK berlaku
        // untuk peran platform (ceo/superadmin) — panel lintas-tenant
        // tertutup bagi admin toko; sebaliknya peran platform juga tidak
        // ikut membuka rute toko (mereka tidak punya id_toko).
        if ($user->role === 'admin' && in_array('kasir', $roles, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized.');
    }
}
