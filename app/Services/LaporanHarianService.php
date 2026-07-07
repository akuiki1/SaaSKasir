<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\Toko;
use App\Models\Transaksi;
use App\Support\Langganan;
use App\Support\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Bangun teks "laporan tutup toko harian" per toko untuk dikirim via WhatsApp.
 * Fitur retensi: tiap malam pemilik dapat ringkasan omzet, transaksi, terlaris,
 * & (tier Warung+) laba — tanpa harus buka aplikasi.
 *
 * Tier-gating menyatu dengan paywall Laporan Keuangan (config 'laporan_keuangan'):
 * - gratis  → omzet, jumlah transaksi, metode bayar, produk terlaris + ajakan upgrade.
 * - Warung+ → tambahan LABA KOTOR harian (untung dari barang = omzet - HPP).
 *
 * Sengaja memakai laba KOTOR (bukan bersih) untuk angka harian: HPP tercatat
 * per-transaksi jadi akurat harian, sedangkan biaya operasional (gaji/sewa)
 * lumpy bulanan → laba bersih harian menyesatkan. Laporan bersih penuh tetap di
 * halaman Laporan Keuangan (rentang bulanan).
 */
class LaporanHarianService
{
    public function __construct(
        private readonly LaporanFinansialService $finansial,
        private readonly TenantContext $tenant,
    ) {}

    /**
     * Rakit pesan WA laporan harian sebuah toko untuk satu tanggal.
     * Mengembalikan null bila toko TIDAK punya transaksi hari itu (jangan
     * ganggu pemilik dengan laporan kosong).
     *
     * PENTING: method ini mengunci TenantContext ke $toko lebih dulu supaya
     * semua query ber-scope (Transaksi/DetailTransaksi) menyaring toko yang
     * benar — pemanggil batch tidak perlu menyetel context sendiri.
     */
    public function bangunPesan(Toko $toko, ?Carbon $tanggal = null): ?string
    {
        $this->tenant->setToko($toko);

        $tanggal ??= Carbon::today();
        $mulai = $tanggal->copy()->startOfDay();
        $selesai = $tanggal->copy()->endOfDay();

        $transaksis = Transaksi::whereBetween('created_at', [$mulai, $selesai])
            ->get(['id_transaksi', 'total_harga', 'metode_pembayaran']);

        $jumlah = $transaksis->count();

        if ($jumlah === 0) {
            return null;
        }

        $omzet = (int) $transaksis->sum('total_harga');
        $rata = intdiv($omzet, $jumlah);

        $baris = [];
        $baris[] = "📊 *SiKasir — Laporan {$toko->nama}*";
        // Nama hari/bulan Indonesia terlepas dari APP_LOCALE (default 'en').
        $baris[] = '🗓️ '.$tanggal->locale('id')->translatedFormat('l, d F Y');
        $baris[] = '';
        $baris[] = '💰 Omzet: *'.$this->rupiah($omzet).'*';
        $baris[] = "🧾 {$jumlah} transaksi · rata-rata ".$this->rupiah($rata);

        // Laba kotor hanya untuk tier yang berhak (menyatu dengan paywall laporan).
        if (Langganan::bolehAkses('laporan_keuangan', $toko->tierEfektif())) {
            $ringkas = $this->finansial->periodSummary($mulai, $selesai);
            $labaKotor = (int) $ringkas['gross_profit'];
            $margin = $omzet > 0 ? round($labaKotor / $omzet * 100) : 0;
            $baris[] = '📈 Laba kotor: *'.$this->rupiah($labaKotor)."* (margin {$margin}%)";
        }

        $metode = $this->rincianMetode($transaksis);
        if ($metode !== []) {
            $baris[] = '';
            $baris[] = '💳 Metode bayar:';
            foreach ($metode as $item) {
                $baris[] = "• {$item['label']}: ".$this->rupiah($item['total']);
            }
        }

        $terlaris = $this->produkTerlaris($mulai, $selesai);
        if ($terlaris !== []) {
            $baris[] = '';
            $baris[] = '🏆 Terlaris hari ini:';
            foreach ($terlaris as $i => $item) {
                $baris[] = ($i + 1).". {$item['nama']} ({$item['qty']})";
            }
        }

        // Ajakan upgrade untuk tier gratis (nudge monetisasi yang halus).
        if (! Langganan::bolehAkses('laporan_keuangan', $toko->tierEfektif())) {
            $baris[] = '';
            $baris[] = '🔒 Ingin tahu laba harianmu? Upgrade ke Warung.';
        }

        $baris[] = '';
        $baris[] = '_Dikirim otomatis oleh SiKasir._';

        return implode("\n", $baris);
    }

    /**
     * Total per metode pembayaran (hanya yang > 0), urut sesuai label baku.
     *
     * @param  Collection<int, Transaksi>  $transaksis
     * @return list<array{label: string, total: int}>
     */
    private function rincianMetode(Collection $transaksis): array
    {
        $agg = $transaksis->groupBy('metode_pembayaran')
            ->map(fn ($g) => (int) $g->sum('total_harga'));

        $hasil = [];
        foreach (LaporanFinansialService::PAYMENT_LABELS as $metode => $label) {
            $total = (int) ($agg[$metode] ?? 0);
            if ($total > 0) {
                $hasil[] = ['label' => $label, 'total' => $total];
            }
        }

        return $hasil;
    }

    /**
     * Tiga produk penyumbang kuantitas terjual terbanyak pada rentang tanggal.
     *
     * @return list<array{nama: string, qty: int}>
     */
    private function produkTerlaris(Carbon $mulai, Carbon $selesai): array
    {
        return DetailTransaksi::whereHas('transaksi', fn ($q) => $q->whereBetween('created_at', [$mulai, $selesai]))
            ->selectRaw('id_produk, SUM(jumlah) as qty')
            ->groupBy('id_produk')
            ->orderByDesc('qty')
            ->take(3)
            ->with('produk:id_produk,nama')
            ->get()
            ->map(fn ($row) => [
                'nama' => $row->produk->nama ?? 'Produk',
                'qty' => (int) $row->qty,
            ])
            ->all();
    }

    private function rupiah(int $nominal): string
    {
        return 'Rp'.number_format($nominal, 0, ',', '.');
    }
}
