<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Langganan SaaS per toko (tier Gratis/Warung/Bisnis). Kolom denormalisasi di
 * sini untuk gating cepat tiap request; riwayat pembayarannya di tabel
 * `langganan_pembayarans` (pola ledger). Tier efektif dihitung di
 * Toko::tierEfektif() dengan mempertimbangkan `langganan_sampai`.
 *
 * `tier`/`langganan_sampai` SENGAJA tidak fillable di model (lihat catatan di
 * Toko) — hanya diubah oleh LanggananService/command/seed, bukan input form,
 * supaya admin toko tidak bisa menaikkan tier-nya sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->enum('tier', ['gratis', 'warung', 'bisnis'])
                ->default('gratis')
                ->after('status');

            // Berlaku sampai (inklusif). NULL pada tier berbayar = perpetual/comp
            // (dipakai grandfather toko lama). NULL pada tier gratis = tak relevan.
            $table->date('langganan_sampai')->nullable()->after('tier');
        });

        // Grandfather toko pertama (klien bayar high-touch) ke Bisnis perpetual.
        DB::table('tokos')
            ->where('slug', 'cemilan-mba-tutut')
            ->update(['tier' => 'bisnis', 'langganan_sampai' => null]);
    }

    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn(['tier', 'langganan_sampai']);
        });
    }
};
