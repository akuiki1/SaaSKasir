<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom role semula ENUM('admin','kasir') di MySQL — terlalu kaku untuk
     * peran platform baru (ceo, superadmin). Diubah jadi string; migration
     * awal (create_users_table) sudah ikut diperbarui, jadi instalasi baru
     * langsung string dan migration ini no-op yang aman di sana.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('kasir')->change();
        });
    }

    public function down(): void
    {
        // Sengaja tidak dikembalikan ke enum — nilai ceo/superadmin yang
        // sudah ada akan ditolak enum lama dan membuat rollback gagal.
    }
};
