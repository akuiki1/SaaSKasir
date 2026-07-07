<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaporanHarianService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;

/**
 * Preview isi laporan tutup toko harian (WhatsApp) untuk toko admin yang
 * sedang login — dipakai kartu "Laporan WA" di dashboard admin supaya fitur
 * terjadwal ini terlihat & bisa dicek tanpa menunggu jam kirim malam.
 *
 * Mengembalikan JSON polos (bukan Inertia) karena dipanggil via fetch dari
 * modal preview; isi pesan dirakit LaporanHarianService yang sama dengan
 * command laporan:harian-wa — apa yang dilihat admin = apa yang terkirim.
 */
class LaporanWaController extends Controller
{
    public function preview(LaporanHarianService $laporan, TenantContext $tenant): JsonResponse
    {
        $toko = $tenant->toko();

        abort_if($toko === null, 404);

        return response()->json([
            // null = belum ada transaksi hari ini (command pun akan skip).
            'pesan' => $laporan->bangunPesan($toko),
        ]);
    }
}
