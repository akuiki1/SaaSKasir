<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotensi transaksi hasil sinkronisasi offline: klien PWA mengirim UUID
     * per-penjualan (client_uid). Bila jaringan berkedip dan respons hilang lalu
     * di-retry, unique index ini + pengecekan di controller mencegah dobel-catat.
     * Nullable karena penjualan online biasa (Inertia) tidak mengirimnya.
     */
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->uuid('client_uid')->nullable()->unique()->after('id_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropUnique(['client_uid']);
            $table->dropColumn('client_uid');
        });
    }
};
