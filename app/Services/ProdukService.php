<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\Promo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Aturan domain produk: default tipe_jual/jenis, aturan khusus produk jasa
 * (tanpa stok/modal/potongan reseller), tarif fee bertingkat, kartu stok
 * saat buat/ubah, generate barcode massal, dan hapus permanen yang aman
 * (hanya produk tanpa riwayat). Dipisah dari ProdukController agar jalur
 * lain (mis. API) bisa memakai ulang tanpa duplikasi.
 */
class ProdukService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function buat(array $validated, ?UploadedFile $fotoUpload = null): Produk
    {
        $validated['jenis'] = $validated['jenis'] ?? 'beli';
        $validated['tipe_jual'] = $validated['tipe_jual'] ?? 'satuan';
        $validated['satuan'] = $validated['satuan'] ?? 'pcs';

        // Produk jasa (tarik tunai/transfer) tidak punya barang fisik: tanpa stok,
        // tanpa potongan reseller, dan tanpa "asal produk" — omzet murni dari fee.
        // harga_jual tidak dipakai untuk jasa (fee diambil dari tarif bertingkat atau
        // diketik kasir tiap transaksi), jadi dipaksa 0 agar tidak menyesatkan.
        if ($validated['tipe_jual'] === 'jasa') {
            $validated['jenis'] = 'beli';
            $validated['stok'] = 0;
            $validated['potongan_reseller'] = 0;
            $validated['harga_jual'] = 0;
        } else {
            $validated['potongan_reseller'] = (int) ($validated['potongan_reseller'] ?? 0);
        }

        $validated['foto'] = $this->resolveFoto($validated, $fotoUpload);

        // Modal barang: produk jasa tidak punya modal (omzet = fee saja); produk
        // 'produksi' dikelola otomatis oleh modul Produksi (batch costing); 'beli'
        // diisi manual.
        $validated['harga_modal'] = $validated['tipe_jual'] === 'jasa' || $validated['jenis'] === 'produksi'
            ? 0
            : (int) ($validated['harga_modal'] ?? 0);

        $produk = Produk::create($validated);

        // Tarif bertingkat hanya relevan untuk produk jasa.
        if ($produk->tipe_jual === 'jasa') {
            $this->syncTarifJasa($produk, $validated['tarifs'] ?? []);
        }

        // Catat saldo awal ke kartu stok bila produk dibuat dengan stok > 0.
        if ((float) $produk->stok != 0.0) {
            $produk->catatMutasiStok(0, (float) $produk->stok, (float) $produk->stok, 'awal', [
                'keterangan' => 'Stok awal saat produk dibuat',
            ]);
        }

        return $produk;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function perbarui(Produk $produk, array $validated, ?UploadedFile $fotoUpload = null): Produk
    {
        $validated['jenis'] = $validated['jenis'] ?? $produk->jenis;
        $validated['tipe_jual'] = $validated['tipe_jual'] ?? $produk->tipe_jual;
        $validated['satuan'] = $validated['satuan'] ?? $produk->satuan;

        // Produk jasa tidak punya barang fisik: tanpa stok, potongan reseller, modal,
        // atau harga_jual (fee diambil dari tarif bertingkat / diketik kasir).
        if ($validated['tipe_jual'] === 'jasa') {
            $validated['jenis'] = 'beli';
            $validated['stok'] = 0;
            $validated['potongan_reseller'] = 0;
            $validated['harga_jual'] = 0;
        } else {
            $validated['potongan_reseller'] = (int) ($validated['potongan_reseller'] ?? 0);
        }

        $stokLama = (float) $produk->stok;

        $validated['foto'] = $this->resolveFoto($validated, $fotoUpload);

        if ($validated['tipe_jual'] === 'jasa') {
            // Jasa tidak punya modal barang (omzet = fee saja).
            $validated['harga_modal'] = 0;
        } elseif ($validated['jenis'] === 'produksi') {
            // Modal dikelola oleh modul Produksi — jangan timpa dari form.
            unset($validated['harga_modal']);
        } else {
            $validated['harga_modal'] = (int) ($validated['harga_modal'] ?? 0);
        }

        $produk->update($validated);

        // Sinkronkan tarif bertingkat: produk jasa ditulis ulang dari form, produk
        // non-jasa dibersihkan (mis. baru dipindah dari jasa ke satuan/curah).
        if ($produk->tipe_jual === 'jasa') {
            $this->syncTarifJasa($produk, $validated['tarifs'] ?? []);
        } else {
            $produk->tarifJasas()->delete();
        }

        // Catat penyesuaian stok manual ke kartu stok bila stok berubah.
        $stokBaru = (float) $produk->stok;
        if ($stokBaru !== $stokLama) {
            $produk->catatMutasiStok($stokLama, $stokBaru, $stokBaru - $stokLama, 'penyesuaian', [
                'keterangan' => 'Penyesuaian stok manual dari halaman produk',
            ]);
        }

        return $produk;
    }

    /**
     * Buat barcode + SKU otomatis untuk semua produk yang belum punya barcode.
     * Produk jasa dilewati karena tidak ada barang fisik untuk discan.
     *
     * @return int jumlah produk yang diberi barcode baru
     */
    public function generateAllBarcodes(): int
    {
        $produks = Produk::where('tipe_jual', '!=', 'jasa')
            ->where(fn ($q) => $q->whereNull('barcode')->orWhere('barcode', ''))
            ->get();

        $dibuat = 0;

        foreach ($produks as $produk) {
            $barcode = Produk::generateUniqueBarcode();
            // SKU lama dipertahankan; kalau kosong baru diturunkan dari barcode.
            $sku = filled($produk->sku) ? $produk->sku : Produk::generateUniqueSku($barcode);

            $produk->update([
                'barcode' => $barcode,
                'sku' => $sku,
            ]);

            $dibuat++;
        }

        return $dibuat;
    }

    /**
     * Apakah produk sudah pernah dipakai pada riwayat bisnis (transaksi/produksi/
     * pesanan)? Ketiganya FK restrictOnDelete — produk seperti ini wajib tetap
     * diarsipkan agar laporan & riwayat tidak rusak, tidak boleh dihapus permanen.
     */
    public function punyaRiwayat(Produk $produk): bool
    {
        return $produk->detailTransaksis()->exists()
            || $produk->produksis()->exists()
            || DB::table('pesanan_items')->where('id_produk', $produk->id_produk)->exists();
    }

    /**
     * Hapus PERMANEN produk yang diarsipkan. Hanya untuk produk salah input /
     * duplikat yang BELUM pernah dipakai — pemanggil wajib mengecek punyaRiwayat()
     * lebih dulu (dijaga juga oleh FK restrictOnDelete di level DB).
     */
    public function hapusPermanen(Produk $produk): void
    {
        DB::transaction(function () use ($produk): void {
            // Bersihkan jejak yang TIDAK memblokir agar tak menyisakan data yatim:
            // kartu stok (saldo awal) & promo khusus produk ini. Tarif jasa ikut
            // terhapus otomatis (FK cascade).
            $produk->stokMutasis()->delete();
            Promo::where('id_produk', $produk->id_produk)->delete();

            $produk->forceDelete();
        });
    }

    /**
     * Tulis ulang tarif fee bertingkat sebuah produk jasa dari input form.
     * Tarif lama dihapus lalu dibuat ulang (set kecil — paling sederhana &
     * konsisten), diurutkan naik berdasarkan batas bawah agar resolusi rapi.
     *
     * @param  array<int, array{min_nominal?: int|string, fee?: int|string}>  $tarifs
     */
    private function syncTarifJasa(Produk $produk, array $tarifs): void
    {
        $produk->tarifJasas()->delete();

        collect($tarifs)
            ->map(fn ($tarif) => [
                'min_nominal' => max(0, (int) ($tarif['min_nominal'] ?? 0)),
                'fee' => max(0, (int) ($tarif['fee'] ?? 0)),
            ])
            ->sortBy('min_nominal')
            ->values()
            ->each(fn ($tarif) => $produk->tarifJasas()->create($tarif));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveFoto(array $validated, ?UploadedFile $fotoUpload): ?string
    {
        if ($fotoUpload) {
            return $fotoUpload->store('produk', 'public');
        }

        return blank($validated['foto'] ?? null) ? null : $validated['foto'];
    }
}
