<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor WhatsApp toko — dipakai storefront publik untuk tautan konfirmasi
 * pesanan. Sebelumnya di-hardcode di PesananPublikController; sekarang jadi
 * data per toko agar tiap toko mengirim ke nomornya sendiri, bukan nomor
 * toko lain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->string('whatsapp', 30)->nullable()->after('slug');
        });

        DB::table('tokos')
            ->where('slug', 'cemilan-mba-tutut')
            ->update(['whatsapp' => '6283114827245']);
    }

    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn('whatsapp');
        });
    }
};
