<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger pembayaran langganan (append-only, pola sama dgn stok_mutasis):
 * mencatat SETIAP perpanjangan langganan — manual (dicatat owner SiKasir via
 * command) sekarang, atau via gateway ('midtrans') nanti lewat webhook.
 * `metode` adalah seam integrasi pembayaran online.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('langganan_pembayarans', function (Blueprint $table) {
            $table->id('id_langganan_pembayaran');

            $table->foreignId('id_toko')
                ->constrained('tokos', 'id_toko')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('tier', ['warung', 'bisnis']);
            $table->unsignedInteger('nominal');

            // 'manual' (dicatat owner) | 'midtrans' (webhook, nanti).
            $table->string('metode', 20)->default('manual');

            $table->date('periode_mulai');
            $table->date('periode_sampai');

            // Pencatat: user pemilik toko / admin. NULL untuk pembayaran otomatis
            // (webhook gateway) yang tak punya aktor manusia.
            $table->foreignId('id_user')
                ->nullable()
                ->constrained('users', 'id')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('catatan')->nullable();

            $table->timestamps();

            $table->index(['id_toko', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('langganan_pembayarans');
    }
};
