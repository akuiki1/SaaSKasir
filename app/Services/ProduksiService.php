<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\Produksi;
use App\Models\ProduksiBiaya;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Batch costing produksi: hitung modal/unit dari total biaya (mode sederhana)
 * atau rincian bahan (mode rinci), lalu tambah stok barang jadi & perbarui
 * modal produk. Diekstrak dari ProduksiController agar jalur lain (mis. API)
 * bisa memakai ulang tanpa duplikasi.
 */
class ProduksiService
{
    /**
     * @param  array{id_produk: int|string, jumlah: int, catatan?: ?string, mode?: ?string, total_biaya?: ?int, biayas?: array<int, array{nama: string, nominal: int}>}  $validated
     *
     * @throws ValidationException
     */
    public function catat(array $validated): Produksi
    {
        return DB::transaction(function () use ($validated): Produksi {
            // Mode eksplisit diutamakan; jika tak ada, deteksi dari ada/tidaknya rincian.
            $mode = $validated['mode'] ?? (! empty($validated['biayas']) ? 'rinci' : 'sederhana');

            if ($mode === 'rinci') {
                $biayas = $validated['biayas'] ?? [];

                if (count($biayas) === 0) {
                    throw ValidationException::withMessages([
                        'biayas' => 'Tambahkan minimal satu rincian biaya bahan.',
                    ]);
                }

                $totalBiaya = (int) collect($biayas)->sum('nominal');
            } else {
                $totalBiaya = (int) ($validated['total_biaya'] ?? 0);
            }

            if ($totalBiaya <= 0) {
                throw ValidationException::withMessages([
                    'total_biaya' => 'Total biaya produksi harus lebih dari 0 (isi total atau rincian bahan).',
                ]);
            }

            $modalPerUnit = (int) round($totalBiaya / $validated['jumlah']);

            $produksi = Produksi::create([
                'id_produk' => $validated['id_produk'],
                'jumlah' => $validated['jumlah'],
                'total_biaya' => $totalBiaya,
                'modal_per_unit' => $modalPerUnit,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Rincian biaya hanya disimpan pada mode rinci.
            if ($mode === 'rinci') {
                foreach ($validated['biayas'] as $biaya) {
                    ProduksiBiaya::create([
                        'id_produksi' => $produksi->id_produksi,
                        'nama' => $biaya['nama'],
                        'nominal' => $biaya['nominal'],
                    ]);
                }
            }

            // Produksi menambah stok barang jadi & memperbarui modal per unit produk.
            $produk = Produk::lockForUpdate()->findOrFail($validated['id_produk']);
            $produk->terapkanMutasiStok(
                (float) $validated['jumlah'],
                'produksi',
                [
                    'keterangan' => 'Batch produksi #'.$produksi->id_produksi,
                    'ref_tipe' => 'Produksi',
                    'id_referensi' => $produksi->id_produksi,
                ]
            );
            $produk->update(['harga_modal' => $modalPerUnit]);

            return $produksi;
        });
    }

    public function hapus(Produksi $produksi): void
    {
        DB::transaction(function () use ($produksi): void {
            // Kembalikan stok barang jadi yang sempat ditambahkan batch ini.
            $produk = Produk::lockForUpdate()->find($produksi->id_produk);

            if ($produk) {
                $produk->terapkanMutasiStok(
                    -(float) $produksi->jumlah,
                    'produksi_batal',
                    [
                        'keterangan' => 'Pembatalan batch produksi #'.$produksi->id_produksi,
                        'ref_tipe' => 'Produksi',
                        'id_referensi' => $produksi->id_produksi,
                    ]
                );
            }

            $produksi->delete(); // biaya ikut terhapus (cascade)
        });
    }
}
