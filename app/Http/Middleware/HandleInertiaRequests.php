<?php

namespace App\Http\Middleware;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\PesananService;
use App\Support\Langganan;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Angka kecil untuk badge sidebar admin; closure agar tak dievaluasi
            // pada partial reload yang tidak memintanya.
            'sidebarBadges' => fn () => $this->sidebarBadges($request),
            // Branding toko aktif (nama, kontak, logo dsb) — closure agar
            // dievaluasi setelah middleware route (mis. ResolveTenant untuk
            // rute publik) selesai menentukan toko aktif di TenantContext.
            'toko' => fn () => $this->tokoBranding(),
            // Status langganan + peta fitur ber-gate → boolean akses, untuk
            // mengunci menu/UI di frontend. Closure (alasan sama dgn 'toko').
            'langganan' => fn () => $this->langgananInfo(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function langgananInfo(): ?array
    {
        $toko = app(TenantContext::class)->toko();

        if (! $toko) {
            return null;
        }

        $tierEfektif = $toko->tierEfektif();

        return [
            'tier' => $tierEfektif,
            'tier_langganan' => $toko->tier,
            'langganan_sampai' => $toko->langganan_sampai?->toDateString(),
            'fitur' => Langganan::fiturUntukTier($tierEfektif),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function tokoBranding(): ?array
    {
        $toko = app(TenantContext::class)->toko();

        if (! $toko) {
            return null;
        }

        return [
            'nama' => $toko->nama,
            'whatsapp' => $toko->whatsapp,
            'alamat' => $toko->alamat,
            'instagram' => $toko->instagram,
            'jam_buka' => $toko->jam_buka,
            'deskripsi' => $toko->deskripsi,
            'logo_url' => Toko::logoUrl($toko->logo),
            'lokasi_lat' => $toko->lokasi_lat !== null ? (float) $toko->lokasi_lat : null,
            'lokasi_lng' => $toko->lokasi_lng !== null ? (float) $toko->lokasi_lng : null,
        ];
    }

    /**
     * Hitungan ringkas untuk badge sidebar admin (pesanan aktif & stok menipis).
     * Hanya admin yang memakainya; peran lain dapat null agar tak ada query sia-sia.
     *
     * @return array{pesananAktif: int, stokMenipis: int}|null
     */
    private function sidebarBadges(Request $request): ?array
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return null;
        }

        return [
            // Pesanan online yang masih perlu diproses (selaras KasirController).
            'pesananAktif' => Pesanan::whereIn('status', PesananService::STATUS_AKTIF)->count(),
            // Stok menipis/habis non-jasa, ambang 5 (selaras DashboardController & Produk).
            'stokMenipis' => Produk::where('tipe_jual', '!=', 'jasa')->where('stok', '<=', 5)->count(),
        ];
    }
}
