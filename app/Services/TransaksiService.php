<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CRUD transaksi sisi admin (input manual, tanpa jasa/curah/promo — versi
 * lebih sederhana dari KasirService). Dipisah dari TransaksiController agar
 * jalur lain (mis. API) bisa memakai ulang tanpa duplikasi.
 */
class TransaksiService
{
    /**
     * @param  array{id_user: int, metode_pembayaran: string, bayar: int, items: array<int, array{id_produk: int|string, jumlah: int|float}>}  $validated
     *
     * @throws ValidationException
     */
    public function buat(array $validated): Transaksi
    {
        return DB::transaction(fn (): Transaksi => $this->simpanTransaksi($validated));
    }

    /**
     * @param  array{id_user: int, metode_pembayaran: string, bayar: int, items: array<int, array{id_produk: int|string, jumlah: int|float}>}  $validated
     *
     * @throws ValidationException
     */
    public function perbarui(Transaksi $transaksi, array $validated): Transaksi
    {
        return DB::transaction(function () use ($transaksi, $validated): Transaksi {
            $this->kembalikanStok($transaksi);
            $transaksi->detailTransaksis()->delete();

            return $this->simpanTransaksi($validated, $transaksi);
        });
    }

    public function hapus(Transaksi $transaksi): void
    {
        DB::transaction(function () use ($transaksi): void {
            $this->kembalikanStok($transaksi);
            $transaksi->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function simpanTransaksi(array $validated, ?Transaksi $transaksi = null): Transaksi
    {
        $totalHarga = 0;
        $details = [];

        foreach ($validated['items'] as $item) {
            $produk = Produk::lockForUpdate()->findOrFail($item['id_produk']);

            if ($produk->stok < $item['jumlah']) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$produk->nama} tidak mencukupi (tersedia: {$produk->stok}).",
                ]);
            }

            $harga = $produk->harga_jual;
            $subtotal = $harga * $item['jumlah'];
            $totalHarga += $subtotal;

            $details[] = [
                'produk' => $produk,
                'jumlah' => $item['jumlah'],
                'harga' => $harga,
                'modal' => $produk->harga_modal, // snapshot HPP/unit saat terjual
                'subtotal' => $subtotal,
            ];
        }

        if ($validated['bayar'] < $totalHarga) {
            throw ValidationException::withMessages([
                'bayar' => 'Jumlah bayar kurang dari total harga.',
            ]);
        }

        $transaksiData = [
            'id_user' => $validated['id_user'],
            'total_harga' => $totalHarga,
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'bayar' => $validated['bayar'],
            'kembalian' => $validated['bayar'] - $totalHarga,
        ];

        if ($transaksi) {
            $transaksi->update($transaksiData);
        } else {
            $transaksi = Transaksi::create($transaksiData);
        }

        foreach ($details as $detail) {
            DetailTransaksi::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'id_produk' => $detail['produk']->id_produk,
                'jumlah' => $detail['jumlah'],
                'harga' => $detail['harga'],
                'modal' => $detail['modal'],
                'subtotal' => $detail['subtotal'],
            ]);

            // Kurangi stok + catat ke kartu stok (produk masih terkunci dari loop validasi).
            $detail['produk']->terapkanMutasiStok(
                -(float) $detail['jumlah'],
                'jual',
                [
                    'keterangan' => 'Penjualan (admin) TRX-'.$transaksi->id_transaksi,
                    'ref_tipe' => 'Transaksi',
                    'id_referensi' => $transaksi->id_transaksi,
                ]
            );
        }

        return $transaksi;
    }

    private function kembalikanStok(Transaksi $transaksi): void
    {
        $transaksi->load('detailTransaksis');

        foreach ($transaksi->detailTransaksis as $detail) {
            if ($detail->id_produk === null) {
                continue; // produk sudah dihapus — lewati pengembalian stok.
            }

            $produk = Produk::lockForUpdate()->find($detail->id_produk);

            if ($produk) {
                // Kembalikan stok + catat ke kartu stok (edit/hapus transaksi).
                $produk->terapkanMutasiStok(
                    (float) $detail->jumlah,
                    'retur',
                    [
                        'keterangan' => 'Pengembalian stok dari edit/hapus TRX-'.$transaksi->id_transaksi,
                        'ref_tipe' => 'Transaksi',
                        'id_referensi' => $transaksi->id_transaksi,
                    ]
                );
            }
        }
    }
}
