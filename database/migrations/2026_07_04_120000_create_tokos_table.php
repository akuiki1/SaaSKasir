<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fondasi multi-tenant: satu baris di sini = satu toko yang berlangganan
 * SiKasir. Toko pertama (Cemilan Mba Tutut) langsung diisi di sini agar
 * migrasi backfill id_toko pada tabel-tabel berikutnya punya baris untuk
 * dirujuk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokos', function (Blueprint $table) {
            $table->id('id_toko');
            $table->string('nama');
            $table->string('slug')->unique();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        DB::table('tokos')->insert([
            'nama' => 'Cemilan Mba Tutut',
            'slug' => 'cemilan-mba-tutut',
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tokos');
    }
};
