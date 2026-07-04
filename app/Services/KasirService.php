<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Promo;
use App\Models\Transaksi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Logika inti "jual langsung" di kasir: tiga cara jual (satuan/curah/jasa),
 * diskon reseller, promo per-produk & promo keranjang otomatis, validasi
 * pembayaran, lalu persist transaksi + kartu stok dalam satu transaksi DB.
 * Dipisah dari KasirController agar jalur lain (mis. API) bisa memakai ulang
 * tanpa duplikasi.
 */
class KasirService
{
    /**
     * @param  array{metode_pembayaran: string, bayar: int, id_pelanggan?: int|string|null, items: array<int, array{id_produk: int|string, jumlah: int|float, nominal?: int|null, fee?: int|null}>}  $validated
     *
     * @throws ValidationException
     */
    public function jual(array $validated, int $userId): Transaksi
    {
        // Idempotensi sinkronisasi offline: bila penjualan dengan client_uid ini
        // sudah tercatat (retry setelah jaringan berkedip), kembalikan yang lama —
        // JANGAN buat & potong stok dua kali. Query auto-scoped ke toko via
        // BelongsToToko; unique index DB adalah jaring pengaman terhadap race.
        // Pemanggil membedakan hit idempoten via $transaksi->wasRecentlyCreated.
        if (! empty($validated['client_uid'])) {
            $existing = Transaksi::where('client_uid', $validated['client_uid'])->first();

            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($validated, $userId): Transaksi {
            $now = now();

            $activePromos = Promo::where('aktif', true)
                ->where('tanggal_mulai', '<=', $now)
                ->where('tanggal_selesai', '>=', $now)
                ->get();

            // Reseller dapat potongan harga rupiah per produk.
            $pelanggan = ! empty($validated['id_pelanggan'])
                ? Pelanggan::find($validated['id_pelanggan'])
                : null;
            $isReseller = $pelanggan?->tipe === 'reseller';

            $subtotal = 0;
            $totalNominalJasa = 0; // titipan transfer/tarik tunai: bukan omzet, tapi dibayar tunai
            $details = [];

            foreach ($validated['items'] as $item) {
                $produk = Produk::lockForUpdate()->findOrFail($item['id_produk']);

                [$detail, $subtotalDelta, $nominalDelta] = match ($produk->tipe_jual) {
                    'jasa' => $this->prosesJasa($produk, $item),
                    'curah' => $this->prosesCurah($produk, $item, $isReseller, $activePromos),
                    default => $this->prosesSatuan($produk, $item, $isReseller, $activePromos),
                };

                $subtotal += $subtotalDelta;
                $totalNominalJasa += $nominalDelta;
                $details[] = $detail;
            }

            [$globalDiskon, $appliedPromoId] = $this->pilihPromoGlobal($activePromos, $subtotal);

            $totalDiskon = $globalDiskon + collect($details)->sum('item_diskon');
            $totalHarga = max(0, $subtotal - $totalDiskon); // omzet (produk + fee jasa), TANPA nominal titipan

            // Tagihan tunai = omzet + nominal titipan jasa. Pelanggan membayar nominal
            // transfer/tarik tunai secara tunai juga, jadi kembalian dihitung dari tagihan
            // ini (bukan dari omzet). Nominal tetap BUKAN omzet — hanya lapisan kas.
            $totalTagihan = $totalHarga + $totalNominalJasa;

            if ($validated['bayar'] < $totalTagihan) {
                throw ValidationException::withMessages([
                    'bayar' => 'Jumlah bayar kurang dari total tagihan.',
                ]);
            }

            $transaksi = Transaksi::create([
                'id_user' => $userId,
                'id_pelanggan' => $pelanggan?->id_pelanggan,
                'id_promo' => $appliedPromoId,
                'total_harga' => $totalHarga,
                'diskon' => $totalDiskon,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'bayar' => $validated['bayar'],
                'kembalian' => $validated['bayar'] - $totalTagihan,
                // Idempotensi sync offline: null untuk penjualan web biasa.
                'client_uid' => $validated['client_uid'] ?? null,
            ]);

            $this->simpanDetailDanStok($transaksi, $details, $userId);

            return $transaksi;
        });
    }

    /** @return array{0: array<string, mixed>, 1: int, 2: int} [detail, subtotal_delta, nominal_delta] */
    private function prosesJasa(Produk $produk, array $item): array
    {
        // Jasa (transfer/tarik tunai): TANPA stok. Omzet = fee saja.
        // Nominal pokok hanya pass-through (titipan), TIDAK masuk omzet.
        $nominalRef = (int) ($item['nominal'] ?? 0);

        if ($nominalRef <= 0) {
            throw ValidationException::withMessages([
                'items' => "Masukkan nominal transfer/tarik untuk {$produk->nama}.",
            ]);
        }

        // Bila layanan punya tarif bertingkat, fee DIHITUNG ULANG dari tabel
        // berdasarkan nominal (anti salah ketik/manipulasi — fee = omzet toko).
        // Tanpa tarif, fee tetap diketik manual seperti sebelumnya.
        $tarifFee = $produk->resolveFeeJasa($nominalRef);

        if ($tarifFee !== null) {
            $fee = $tarifFee;
        } else {
            $fee = (int) ($item['fee'] ?? 0);

            if ($fee <= 0) {
                throw ValidationException::withMessages([
                    'items' => "Masukkan biaya admin (fee) untuk {$produk->nama}.",
                ]);
            }
        }

        $detail = [
            'produk' => $produk,
            'jumlah' => 1,
            'harga' => $fee,
            'modal' => 0, // jasa tidak punya HPP
            'subtotal_after_promo' => $fee,
            'item_diskon' => 0, // promo tidak berlaku untuk fee jasa
            'nominal' => $nominalRef,
            'is_jasa' => true,
        ];

        return [$detail, $fee, $nominalRef];
    }

    /** @return array{0: array<string, mixed>, 1: int, 2: int} */
    private function prosesCurah(Produk $produk, array $item, bool $isReseller, Collection $activePromos): array
    {
        $harga = $this->hargaEfektif($produk, $isReseller);

        // Curah (bensin/bawang): kasir input NOMINAL rupiah → qty = nominal ÷ harga/satuan.
        // subtotal = nominal persis; qty (3 desimal) dipakai untuk potong stok & HPP.
        $nominal = (int) ($item['nominal'] ?? 0);

        if ($nominal <= 0) {
            throw ValidationException::withMessages([
                'items' => "Masukkan nominal pembelian untuk {$produk->nama}.",
            ]);
        }

        if ($harga <= 0) {
            throw ValidationException::withMessages([
                'items' => "Harga per {$produk->satuan} untuk {$produk->nama} belum diatur.",
            ]);
        }

        $qty = round($nominal / $harga, 3);
        $itemSubtotal = $nominal;

        $this->assertStok($produk, $qty);

        $itemDiskon = $this->hitungDiskonItem($produk, $activePromos, $qty, $harga, $itemSubtotal);

        $detail = [
            'produk' => $produk,
            'jumlah' => $qty,
            'harga' => $harga,
            'modal' => $produk->harga_modal, // snapshot HPP/unit saat terjual
            'subtotal_after_promo' => max(0, $itemSubtotal - $itemDiskon),
            'item_diskon' => $itemDiskon,
        ];

        return [$detail, $itemSubtotal, 0];
    }

    /** @return array{0: array<string, mixed>, 1: int, 2: int} */
    private function prosesSatuan(Produk $produk, array $item, bool $isReseller, Collection $activePromos): array
    {
        $harga = $this->hargaEfektif($produk, $isReseller);
        $qty = (float) $item['jumlah'];
        $itemSubtotal = (int) round($harga * $qty);

        $this->assertStok($produk, $qty);

        $itemDiskon = $this->hitungDiskonItem($produk, $activePromos, $qty, $harga, $itemSubtotal);

        $detail = [
            'produk' => $produk,
            'jumlah' => $qty,
            'harga' => $harga,
            'modal' => $produk->harga_modal,
            'subtotal_after_promo' => max(0, $itemSubtotal - $itemDiskon),
            'item_diskon' => $itemDiskon,
        ];

        return [$detail, $itemSubtotal, 0];
    }

    private function assertStok(Produk $produk, float $qty): void
    {
        if ($produk->stok < $qty) {
            throw ValidationException::withMessages([
                'items' => "Stok {$produk->nama} tidak mencukupi (tersedia: {$produk->stok} {$produk->satuan}).",
            ]);
        }
    }

    private function hargaEfektif(Produk $produk, bool $isReseller): int
    {
        return $isReseller
            ? max(0, $produk->harga_jual - $produk->potongan_reseller)
            : $produk->harga_jual;
    }

    /** Diskon promo spesifik-produk (persen / bundling beli-gratis / nominal). */
    private function hitungDiskonItem(Produk $produk, Collection $activePromos, float $qty, int $harga, int $itemSubtotal): int
    {
        $prodPromo = $activePromos->where('id_produk', $produk->id_produk)->first();

        if (! $prodPromo) {
            return 0;
        }

        if ($prodPromo->tipe === 'persen') {
            // Diskon persen tidak lagi dibuat dari form, tapi promo lama
            // tetap dihormati agar data historis konsisten.
            return (int) ($itemSubtotal * ($prodPromo->nilai / 100));
        }

        if ($prodPromo->tipe === 'bundling') {
            // Beli X gratis Y — hanya untuk produk satuan (qty bilangan bulat).
            $grup = (int) $prodPromo->beli_qty + (int) $prodPromo->gratis_qty;

            if ($produk->tipe_jual === 'satuan' && $grup > 0 && $prodPromo->gratis_qty > 0) {
                $gratis = intdiv((int) floor($qty), $grup) * (int) $prodPromo->gratis_qty;

                return (int) ($gratis * $harga);
            }

            return 0;
        }

        // nominal
        return (int) ($prodPromo->nilai * $qty);
    }

    /**
     * Promo keranjang (global, id_produk null) diterapkan OTOMATIS: dari promo
     * aktif yang syarat minimal belanjanya terpenuhi, pilih yang diskonnya
     * paling besar untuk pelanggan. Kasir tidak perlu memilih manual.
     *
     * @return array{0: int, 1: int|null} [diskon, id_promo]
     */
    private function pilihPromoGlobal(Collection $activePromos, int $subtotal): array
    {
        $globalDiskon = 0;
        $appliedPromoId = null;

        foreach ($activePromos->whereNull('id_produk') as $globalPromo) {
            if ($globalPromo->minimal_belanja && $subtotal < $globalPromo->minimal_belanja) {
                continue;
            }

            $diskon = $globalPromo->tipe === 'persen'
                ? (int) ($subtotal * ($globalPromo->nilai / 100))
                : (int) $globalPromo->nilai;

            if ($diskon > $globalDiskon) {
                $globalDiskon = $diskon;
                $appliedPromoId = $globalPromo->id_promo;
            }
        }

        return [$globalDiskon, $appliedPromoId];
    }

    /** Persist baris detail transaksi + mutasi stok (jasa dikecualikan dari stok). */
    private function simpanDetailDanStok(Transaksi $transaksi, array $details, int $userId): void
    {
        foreach ($details as $detail) {
            DetailTransaksi::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'id_produk' => $detail['produk']->id_produk,
                'jumlah' => $detail['jumlah'],
                'harga' => $detail['harga'],
                'modal' => $detail['modal'],
                'subtotal' => $detail['subtotal_after_promo'],
                'nominal' => $detail['nominal'] ?? null,
            ]);

            // Jasa tidak menyentuh stok (tanpa kartu stok).
            if (! empty($detail['is_jasa'])) {
                continue;
            }

            // Kurangi stok + catat ke kartu stok (produk masih terkunci dari loop validasi).
            $detail['produk']->terapkanMutasiStok(
                -(float) $detail['jumlah'],
                'jual',
                [
                    'keterangan' => 'Penjualan TRX-'.$transaksi->id_transaksi,
                    'ref_tipe' => 'Transaksi',
                    'id_referensi' => $transaksi->id_transaksi,
                    'id_user' => $userId,
                ]
            );
        }
    }
}
