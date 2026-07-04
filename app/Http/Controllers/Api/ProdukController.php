<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\JsonResponse;

/**
 * Katalog produk untuk klien API (mis. app kasir mobile/PWA mendatang) —
 * field yang sama dengan grid kasir web (KasirController::transaksi), tanpa
 * data sensitif (harga_modal). Jasa dikecualikan: alurnya beda (tarif fee
 * bertingkat), bukan grid jual biasa.
 */
class ProdukController extends Controller
{
    public function index(): JsonResponse
    {
        $produks = Produk::query()
            ->with('kategori:id_kategori,nama_kategori')
            ->where('tipe_jual', '!=', 'jasa')
            ->orderBy('nama')
            ->get()
            ->map(fn (Produk $produk) => [
                'id_produk' => $produk->id_produk,
                'nama' => $produk->nama,
                'kategori' => $produk->kategori?->nama_kategori,
                'tipe_jual' => $produk->tipe_jual,
                'satuan' => $produk->satuan,
                'harga_jual' => $produk->harga_jual,
                'potongan_reseller' => $produk->potongan_reseller,
                'stok' => $produk->stok,
                'barcode' => $produk->barcode,
                'foto_url' => Produk::fotoUrl($produk->foto),
            ])
            ->values();

        return response()->json(['produks' => $produks]);
    }
}
