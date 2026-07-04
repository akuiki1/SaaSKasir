<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * barcode/sku sekarang unik per toko, bukan unik global — dua toko boleh
 * pakai barcode/sku yang sama tanpa bentrok.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->unsignedBigInteger('id_toko')->nullable()->after('id_produk')->index();
        });

        DB::table('produks')->update([
            'id_toko' => DB::table('tokos')->value('id_toko'),
        ]);

        Schema::table('produks', function (Blueprint $table) {
            $table->dropUnique('produks_barcode_unique');
            $table->dropUnique('produks_sku_unique');
            $table->unique(['id_toko', 'barcode']);
            $table->unique(['id_toko', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropUnique(['id_toko', 'barcode']);
            $table->dropUnique(['id_toko', 'sku']);
            $table->unique('barcode');
            $table->unique('sku');
            $table->dropColumn('id_toko');
        });
    }
};
