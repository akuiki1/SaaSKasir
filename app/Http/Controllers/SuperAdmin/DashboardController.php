<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LangananPembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard Super Admin: pos kerja operasional platform — apa yang perlu
 * DIKERJAKAN hari ini (langganan segera berakhir, toko baru yang perlu
 * disapa), bukan analisis bisnis (itu dashboard CEO). Semua angka di sini
 * mengarah ke halaman kelola (toko/admin) sebagai tindak lanjut.
 */
class DashboardController extends Controller
{
    /** Jendela hari untuk peringatan langganan segera berakhir. */
    private const EXPIRING_DAYS = 14;

    public function index(): Response
    {
        $semuaToko = Toko::query()->get(['id_toko', 'nama', 'slug', 'status', 'tier', 'langganan_sampai', 'created_at']);

        $berbayarAktif = $semuaToko->filter(fn (Toko $toko) => $toko->tierEfektif() !== 'gratis');

        return Inertia::render('superadmin/Dashboard', [
            'nama' => Auth::user()?->name ?? 'Super Admin',
            'today_label' => Carbon::now()->locale('id')->translatedFormat('l, d F Y'),
            'stats' => [
                'total_toko' => $semuaToko->count(),
                'toko_aktif' => $semuaToko->where('status', 'aktif')->count(),
                'toko_nonaktif' => $semuaToko->where('status', '!=', 'aktif')->count(),
                'total_admin' => User::where('role', 'admin')->count(),
                'total_kasir' => User::where('role', 'kasir')->count(),
                'langganan_berbayar' => $berbayarAktif->count(),
                'segera_berakhir' => $this->langgananSegeraBerakhir($semuaToko)->count(),
            ],
            'segera_berakhir' => $this->langgananSegeraBerakhir($semuaToko)
                ->take(8)
                ->map(fn (Toko $toko) => [
                    'id_toko' => $toko->id_toko,
                    'nama' => $toko->nama,
                    'tier' => $toko->tier,
                    'sampai' => $toko->langganan_sampai?->locale('id')->translatedFormat('d M Y'),
                    'sisa_hari' => (int) Carbon::today()->diffInDays($toko->langganan_sampai, false),
                ])
                ->values()
                ->all(),
            'pembayaran_terbaru' => $this->pembayaranTerbaru(),
            'toko_terbaru' => $semuaToko
                ->sortByDesc('created_at')
                ->take(5)
                ->map(fn (Toko $toko) => [
                    'id_toko' => $toko->id_toko,
                    'nama' => $toko->nama,
                    'slug' => $toko->slug,
                    'status' => $toko->status,
                    'tier' => $toko->tierEfektif(),
                    'tanggal' => $toko->created_at?->locale('id')->translatedFormat('d M Y') ?? '-',
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * Toko berbayar yang masa langganannya habis dalam <= EXPIRING_DAYS hari
     * (termasuk yang baru saja lewat beberapa hari — masih layak dikejar).
     *
     * @param  \Illuminate\Support\Collection<int, Toko>  $semuaToko
     * @return \Illuminate\Support\Collection<int, Toko>
     */
    private function langgananSegeraBerakhir($semuaToko)
    {
        $batas = Carbon::today()->addDays(self::EXPIRING_DAYS);

        return $semuaToko
            ->filter(fn (Toko $toko) => $toko->tier !== 'gratis'
                && $toko->langganan_sampai !== null
                && $toko->langganan_sampai->lte($batas)
                && $toko->langganan_sampai->gte(Carbon::today()->subDays(7)))
            ->sortBy('langganan_sampai')
            ->values();
    }

    /**
     * 5 pembayaran langganan terakhir (ledger lintas-tenant).
     *
     * @return list<array{toko: string, tier: string, nominal: int, metode: string, tanggal: string}>
     */
    private function pembayaranTerbaru(): array
    {
        return LangananPembayaran::with('toko:id_toko,nama')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn (LangananPembayaran $bayar) => [
                'toko' => $bayar->toko?->nama ?? 'Toko Terhapus',
                'tier' => $bayar->tier,
                'nominal' => (int) $bayar->nominal,
                'metode' => $bayar->metode,
                'tanggal' => $bayar->created_at?->locale('id')->translatedFormat('d M Y') ?? '-',
            ])
            ->all();
    }
}
