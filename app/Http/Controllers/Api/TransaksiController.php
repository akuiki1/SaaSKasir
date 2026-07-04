<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KasirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Endpoint API pertama sebagai bukti konsep "API-first": memakai ulang
 * KasirService yang sama persis dengan yang dipakai halaman kasir web —
 * bukti bahwa ekstraksi logika ke service layer benar-benar memungkinkan
 * jalur baru (di sini: API) tanpa duplikasi logika bisnis.
 */
class TransaksiController extends Controller
{
    public function store(Request $request, KasirService $service): JsonResponse
    {
        $validated = $request->validate([
            'metode_pembayaran' => ['required', Rule::in(['cash', 'qris', 'transfer'])],
            'bayar' => ['required', 'integer', 'min:0'],
            'id_pelanggan' => ['nullable', 'exists:pelanggans,id_pelanggan'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_produk' => ['required', 'exists:produks,id_produk'],
            // numeric (bukan integer) agar produk curah bisa dijual pecahan (mis. 1.429 liter).
            'items.*.jumlah' => ['required', 'numeric', 'gt:0'],
            'items.*.nominal' => ['nullable', 'integer', 'min:1'],
            'items.*.fee' => ['nullable', 'integer', 'min:0'],
        ]);

        $transaksi = $service->jual($validated, $request->user()->id);
        $transaksi->load('detailTransaksis.produk');

        return response()->json([
            'id_transaksi' => $transaksi->id_transaksi,
            'kode' => 'TRX-'.$transaksi->id_transaksi,
            'total_harga' => $transaksi->total_harga,
            'diskon' => $transaksi->diskon,
            'bayar' => $transaksi->bayar,
            'kembalian' => $transaksi->kembalian,
            'items' => $transaksi->detailTransaksis->map(fn ($detail) => [
                'nama_produk' => $detail->produk?->nama,
                'jumlah' => $detail->jumlah,
                'harga' => $detail->harga,
                'subtotal' => $detail->subtotal,
            ])->values(),
        ], 201);
    }
}
