<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Promo;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Storefront publik. Dipakai oleh rute akar `/` (toko default, fase
 * satu-instance-satu-klien) maupun `toko/{tokoSlug}` (siap multi-toko) —
 * keduanya lewat middleware ResolveTenant, jadi query di sini otomatis
 * ter-scope ke toko yang benar tanpa perlu tahu dari rute mana ia dipanggil.
 */
class HomeController extends Controller
{
    public function index(): Response
    {
        $now = now();

        // Promo aktif yang masih berlaku & spesifik per produk (bukan promo global "Semua Produk").
        $activePromos = Promo::where('aktif', true)
            ->whereNotNull('id_produk')
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_selesai', '>=', $now)
            ->orderBy('tanggal_selesai')
            ->get()
            ->groupBy('id_produk');

        // Bentuk keterangan promo (jenis diskon + harga akhir + sisa berlaku) untuk sebuah produk.
        // Harga promo dihitung di server agar konsisten dengan klien (hindari hydration mismatch).
        $promoFor = function (int $idProduk, $hargaJual = null) use ($activePromos, $now): ?array {
            $promo = $activePromos->get($idProduk)?->first();

            if (! $promo) {
                return null;
            }

            $sisaHari = (int) ceil(($promo->tanggal_selesai->getTimestamp() - $now->getTimestamp()) / 86400);

            $label = $promo->tipe === 'persen'
                ? 'Diskon '.rtrim(rtrim(number_format($promo->nilai, 2, ',', '.'), '0'), ',').'%'
                : 'Diskon Rp'.number_format($promo->nilai, 0, ',', '.');

            // Harga akhir setelah diskon (untuk tampilan harga coret).
            $hargaPromo = null;

            if ($hargaJual !== null) {
                $harga = (float) $hargaJual;
                $potongan = $promo->tipe === 'persen'
                    ? $harga * ((float) $promo->nilai / 100)
                    : (float) $promo->nilai;
                $hargaPromo = (int) round(max(0, $harga - $potongan));
            }

            return [
                'nama' => $promo->nama,
                'label' => $label,
                'tipe' => $promo->tipe,
                'nilai' => (float) $promo->nilai,
                'harga_promo' => $hargaPromo,
                'sisa_hari' => max(0, $sisaHari),
                // Sertakan jam akhir hari agar hitung mundur di klien presisi sampai detik.
                'tanggal_selesai' => $promo->tanggal_selesai->format('Y-m-d'),
                'berakhir_pada' => $promo->tanggal_selesai->copy()->endOfDay()->toIso8601String(),
            ];
        };

        $bestSellers = Produk::select(
            'produks.id_produk',
            'produks.nama',
            'produks.harga_jual',
            'produks.stok',
            'produks.foto'
        )
            ->selectRaw('COALESCE(SUM(detail_transaksis.jumlah), 0) as total_terjual')
            ->leftJoin('detail_transaksis', 'produks.id_produk', '=', 'detail_transaksis.id_produk')
            // Storefront berbasis qty: hanya produk satuan yang bisa dipesan/dibeli online.
            ->where('produks.tipe_jual', 'satuan')
            ->groupBy('produks.id_produk', 'produks.nama', 'produks.harga_jual', 'produks.stok', 'produks.foto')
            ->orderByDesc('total_terjual')
            // Ambil kandidat lebih banyak dari yang ditampilkan supaya produk
            // berfoto bisa "naik" ke etalase walau peringkat penjualannya sedikit
            // di bawah — tapi tetap dibatasi ke 10 terlaris agar kartu yang tampil
            // selalu produk yang benar-benar laku (badge "Best seller" tak nyasar).
            ->take(10)
            ->get()
            // Kurasi etalase: di antara 10 terlaris, produk berfoto tampil lebih
            // dulu (tetap urut penjualan dalam tiap grup), lalu ambil 5 teratas.
            // Tujuannya carousel landing tampil maksimal sekaligus jadi insentif
            // owner mengunggah foto produk unggulannya.
            ->sortByDesc(fn ($p) => [$p->foto ? 1 : 0, (int) $p->total_terjual])
            ->take(5)
            ->values()
            ->map(fn ($p) => [
                'id_produk' => $p->id_produk,
                'nama' => $p->nama,
                'harga_jual' => $p->harga_jual,
                'stok' => (int) $p->stok,
                'foto_url' => Produk::fotoUrl($p->foto),
                'total_terjual' => (int) $p->total_terjual,
                'promo' => $promoFor($p->id_produk, $p->harga_jual),
            ]);

        $allProducts = Produk::with('kategori')
            ->where('tipe_jual', 'satuan')
            ->orderBy('nama')
            ->get()
            ->map(fn (Produk $p) => [
                'id_produk' => $p->id_produk,
                'nama' => $p->nama,
                'kategori' => $p->kategori?->nama_kategori,
                'harga_jual' => $p->harga_jual,
                'stok' => $p->stok,
                'foto_url' => Produk::fotoUrl($p->foto),
                'promo' => $promoFor($p->id_produk, $p->harga_jual),
            ])
            // Produk berfoto tampil paling atas, lalu yang sedang promo; urutan nama
            // tetap dalam tiap grup (sort PHP 8 stabil). Konsisten dengan etalase
            // best seller: produk berfoto selalu lebih dulu muncul di landing.
            ->sortBy(fn ($p) => [
                $p['foto_url'] === null ? 1 : 0,
                $p['promo'] === null ? 1 : 0,
            ])
            ->values();

        return Inertia::render('Welcome', [
            'bestSellers' => $bestSellers,
            'allProducts' => $allProducts,
        ]);
    }
}
