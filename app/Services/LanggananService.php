<?php

namespace App\Services;

use App\Models\LangananPembayaran;
use App\Models\Toko;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logika langganan: catat pembayaran/perpanjangan + set tier & masa berlaku
 * toko dalam satu transaksi. Class polos + DB::transaction (pola service
 * SiKasir). Ini SEAM integrasi pembayaran: sekarang dipanggil command manual
 * (metode='manual'); nanti webhook Midtrans memanggil method yang sama persis
 * dengan metode='midtrans' — perilaku perpanjangan tetap satu sumber.
 */
class LanggananService
{
    public function catatPembayaran(
        Toko $toko,
        string $tier,
        int $bulan,
        ?int $nominal = null,
        string $metode = 'manual',
        ?int $userId = null,
        ?string $catatan = null,
    ): LangananPembayaran {
        return DB::transaction(function () use ($toko, $tier, $bulan, $nominal, $metode, $userId, $catatan): LangananPembayaran {
            $toko->refresh();

            // Perpanjangan menumpuk: kalau langganan lama masih aktif (tier sama
            // & belum kedaluwarsa), mulai dari akhir periode itu; kalau sudah
            // lewat/gratis, mulai dari hari ini.
            $mulaiDari = ($toko->tierEfektif() !== 'gratis' && $toko->langganan_sampai !== null)
                ? Carbon::parse($toko->langganan_sampai)
                : Carbon::today();

            if ($mulaiDari->lt(Carbon::today())) {
                $mulaiDari = Carbon::today();
            }

            $periodeSampai = $mulaiDari->copy()->addMonths($bulan);

            $nominal ??= (int) (config("langganan.tiers.{$tier}.harga", 0)) * $bulan;

            $pembayaran = LangananPembayaran::create([
                'id_toko' => $toko->id_toko,
                'tier' => $tier,
                'nominal' => $nominal,
                'metode' => $metode,
                'periode_mulai' => $mulaiDari->toDateString(),
                'periode_sampai' => $periodeSampai->toDateString(),
                'id_user' => $userId,
                'catatan' => $catatan,
            ]);

            // tier & langganan_sampai bukan fillable (cegah bypass paywall) —
            // set eksplisit lewat forceFill dari kode server tepercaya ini.
            $toko->forceFill([
                'tier' => $tier,
                'langganan_sampai' => $periodeSampai->toDateString(),
            ])->save();

            return $pembayaran;
        });
    }
}
