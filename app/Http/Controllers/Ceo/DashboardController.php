<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard CEO: pemantauan SELURUH platform, baca-saja — kesehatan bisnis
 * SiKasir dalam satu layar (pertumbuhan toko, GMV, MRR, distribusi tier,
 * toko paling aktif). Tidak ada satu pun aksi tulis di sini; pengelolaan
 * (status toko, langganan, reset password) adalah ranah Super Admin.
 *
 * CATATAN LINTAS-TENANT: user ceo tidak punya id_toko → TenantContext null →
 * global scope BelongsToToko nonaktif. withoutGlobalScope('toko') tetap
 * ditulis EKSPLISIT di tiap query agar niat lintas-tenant terbaca dan tidak
 * bergantung diam-diam pada resolusi context.
 */
class DashboardController extends Controller
{
    public function index(): Response
    {
        // Satu kali muat ringan semua toko — dipakai beberapa agregat sekaligus.
        $semuaToko = Toko::query()->get(['id_toko', 'nama', 'slug', 'status', 'tier', 'langganan_sampai', 'created_at']);

        return Inertia::render('ceo/Dashboard', [
            'ceo_name' => Auth::user()?->name ?? 'CEO',
            'today_label' => Carbon::now()->locale('id')->translatedFormat('l, d F Y'),
            'toko_stats' => $this->tokoStats($semuaToko),
            'user_stats' => $this->userStats(),
            'langganan_stats' => $this->langgananStats($semuaToko),
            'gmv_stats' => $this->gmvStats(),
            'trend' => $this->trend14d(),
            'pertumbuhan_toko' => $this->pertumbuhanToko($semuaToko),
            'top_toko' => $this->topToko($semuaToko),
            'toko_terbaru' => $this->tokoTerbaru(),
        ]);
    }

    /**
     * @param  Collection<int, Toko>  $semuaToko
     * @return array{total: int, aktif: int, nonaktif: int, baru_bulan_ini: int}
     */
    private function tokoStats(Collection $semuaToko): array
    {
        return [
            'total' => $semuaToko->count(),
            'aktif' => $semuaToko->where('status', 'aktif')->count(),
            'nonaktif' => $semuaToko->where('status', '!=', 'aktif')->count(),
            'baru_bulan_ini' => $semuaToko
                ->filter(fn (Toko $toko) => $toko->created_at?->gte(Carbon::now()->startOfMonth()))
                ->count(),
        ];
    }

    /**
     * @return array{total: int, admin: int, kasir: int}
     */
    private function userStats(): array
    {
        // User tidak ber-global-scope (lihat model) → query ini memang lintas
        // toko. Peran platform tidak ikut dihitung — yang dipantau user toko.
        $perRole = User::query()
            ->whereIn('role', ['admin', 'kasir'])
            ->selectRaw('role, COUNT(*) as jumlah')
            ->groupBy('role')
            ->pluck('jumlah', 'role');

        return [
            'total' => (int) $perRole->sum(),
            'admin' => (int) ($perRole['admin'] ?? 0),
            'kasir' => (int) ($perRole['kasir'] ?? 0),
        ];
    }

    /**
     * Distribusi tier EFEKTIF (kedaluwarsa dihitung gratis) + estimasi MRR.
     *
     * @param  Collection<int, Toko>  $semuaToko
     * @return array{mrr: int, berbayar: int, distribusi: list<array{tier: string, label: string, jumlah: int}>}
     */
    private function langgananStats(Collection $semuaToko): array
    {
        $tiers = config('langganan.tiers');
        $perTier = $semuaToko->groupBy(fn (Toko $toko) => $toko->tierEfektif());

        $mrr = 0;
        $distribusi = [];

        foreach ($tiers as $key => $tier) {
            $jumlah = $perTier->get($key, collect())->count();
            $mrr += $jumlah * (int) $tier['harga'];

            $distribusi[] = [
                'tier' => $key,
                'label' => $tier['label'],
                'jumlah' => $jumlah,
            ];
        }

        return [
            'mrr' => $mrr,
            'berbayar' => $semuaToko->count() - $perTier->get('gratis', collect())->count(),
            'distribusi' => $distribusi,
        ];
    }

    /**
     * GMV (nilai transaksi seluruh toko) & jumlah transaksi: hari ini vs
     * kemarin, dan bulan berjalan vs bulan lalu (bulan lalu utuh — pembanding
     * kasar, cukup untuk arah tren).
     *
     * @return array<string, array{gmv: int, transaksi: int}>
     */
    private function gmvStats(): array
    {
        $rentang = [
            'hari_ini' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'kemarin' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'bulan_ini' => [Carbon::now()->startOfMonth(), Carbon::now()],
            'bulan_lalu' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
        ];

        $hasil = [];
        foreach ($rentang as $kunci => [$mulai, $sampai]) {
            $baris = Transaksi::withoutGlobalScope('toko')
                ->whereBetween('created_at', [$mulai, $sampai])
                ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total_harga), 0) as gmv')
                ->first();

            $hasil[$kunci] = [
                'gmv' => (int) $baris->gmv,
                'transaksi' => (int) $baris->jumlah,
            ];
        }

        return $hasil;
    }

    /**
     * Tren GMV & transaksi platform 14 hari terakhir (termasuk hari ini).
     *
     * @return list<array{date: string, label: string, gmv: int, transaksi: int}>
     */
    private function trend14d(): array
    {
        $mulai = Carbon::today()->subDays(13)->startOfDay();

        $perTanggal = Transaksi::withoutGlobalScope('toko')
            ->whereBetween('created_at', [$mulai, Carbon::today()->endOfDay()])
            ->get(['total_harga', 'created_at'])
            ->groupBy(fn (Transaksi $trx) => $trx->created_at->format('Y-m-d'));

        $hari = [];
        for ($i = 13; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);
            $grup = $perTanggal->get($tanggal->format('Y-m-d'), collect());

            $hari[] = [
                'date' => $tanggal->toDateString(),
                'label' => $tanggal->format('d/m'),
                'gmv' => (int) $grup->sum('total_harga'),
                'transaksi' => $grup->count(),
            ];
        }

        return $hari;
    }

    /**
     * Toko baru per bulan + total kumulatif, 6 bulan terakhir — kurva adopsi
     * menuju gerbang 100/1.000 toko di roadmap.
     *
     * @param  Collection<int, Toko>  $semuaToko
     * @return list<array{label: string, baru: int, kumulatif: int}>
     */
    private function pertumbuhanToko(Collection $semuaToko): array
    {
        $bulanIni = Carbon::now()->startOfMonth();
        $hasil = [];

        for ($i = 5; $i >= 0; $i--) {
            $awalBulan = $bulanIni->copy()->subMonthsNoOverflow($i);
            $akhirBulan = $awalBulan->copy()->endOfMonth();

            $hasil[] = [
                'label' => $awalBulan->locale('id')->translatedFormat('M y'),
                'baru' => $semuaToko->filter(
                    fn (Toko $toko) => $toko->created_at?->between($awalBulan, $akhirBulan)
                )->count(),
                'kumulatif' => $semuaToko->filter(
                    fn (Toko $toko) => $toko->created_at !== null && $toko->created_at->lte($akhirBulan)
                )->count(),
            ];
        }

        return $hasil;
    }

    /**
     * 5 toko dengan omzet tertinggi bulan berjalan.
     *
     * @param  Collection<int, Toko>  $semuaToko
     * @return list<array{nama: string, slug: string|null, tier: string, omzet: int, transaksi: int}>
     */
    private function topToko(Collection $semuaToko): array
    {
        $perToko = Transaksi::withoutGlobalScope('toko')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->selectRaw('id_toko, COUNT(*) as jumlah, COALESCE(SUM(total_harga), 0) as omzet')
            ->groupBy('id_toko')
            ->orderByDesc('omzet')
            ->limit(5)
            ->get();

        $tokoById = $semuaToko->keyBy('id_toko');

        return $perToko
            ->map(function ($baris) use ($tokoById) {
                $toko = $tokoById->get($baris->id_toko);

                return [
                    'nama' => $toko?->nama ?? 'Toko Terhapus',
                    'slug' => $toko?->slug,
                    'tier' => $toko?->tierEfektif() ?? 'gratis',
                    'omzet' => (int) $baris->omzet,
                    'transaksi' => (int) $baris->jumlah,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * 5 toko yang paling baru mendaftar.
     *
     * @return list<array{nama: string, slug: string|null, tier: string, status: string, tanggal: string}>
     */
    private function tokoTerbaru(): array
    {
        return Toko::query()
            ->latest('created_at')
            ->take(5)
            ->get(['nama', 'slug', 'status', 'tier', 'langganan_sampai', 'created_at'])
            ->map(fn (Toko $toko) => [
                'nama' => $toko->nama,
                'slug' => $toko->slug,
                'tier' => $toko->tierEfektif(),
                'status' => $toko->status,
                'tanggal' => $toko->created_at?->locale('id')->translatedFormat('d M Y') ?? '-',
            ])
            ->all();
    }
}
