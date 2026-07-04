<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->unsignedBigInteger('id_toko')->nullable()->after('id_pesanan')->index();
        });

        DB::table('pesanans')->update([
            'id_toko' => DB::table('tokos')->value('id_toko'),
        ]);
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn('id_toko');
        });
    }
};
