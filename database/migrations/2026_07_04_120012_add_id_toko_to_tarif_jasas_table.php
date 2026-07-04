<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarif_jasas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_toko')->nullable()->after('id_tarif_jasa')->index();
        });

        DB::table('tarif_jasas')->update([
            'id_toko' => DB::table('tokos')->value('id_toko'),
        ]);
    }

    public function down(): void
    {
        Schema::table('tarif_jasas', function (Blueprint $table) {
            $table->dropColumn('id_toko');
        });
    }
};
