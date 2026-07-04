<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data branding per toko — sebelumnya hardcode "Cemilan Mba Tutut" di ~7 file
 * frontend (Welcome.vue, struk.ts, AuthLayout.vue, AppLogo*.vue, dsb). Kolom
 * `logo` menyimpan path relatif hasil upload (pola sama dengan Produk::foto);
 * biarkan null untuk toko yang belum unggah agar frontend fallback ke logo
 * default SiKasir, bukan logo toko lain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('whatsapp');
            $table->string('instagram')->nullable()->after('alamat');
            $table->string('jam_buka')->nullable()->after('instagram');
            $table->text('deskripsi')->nullable()->after('jam_buka');
            $table->string('logo')->nullable()->after('deskripsi');
            $table->decimal('lokasi_lat', 10, 7)->nullable()->after('logo');
            $table->decimal('lokasi_lng', 10, 7)->nullable()->after('lokasi_lat');
        });

        DB::table('tokos')
            ->where('slug', 'cemilan-mba-tutut')
            ->update([
                'alamat' => 'Jl. Putera Harapan, Matang Ginalun, Barabai, Hulu Sungai Tengah, Kalimantan Selatan',
                'instagram' => 'rumahcemilan_mbatutut12barabai',
                'jam_buka' => 'Setiap hari (kecuali Jumat), 09.00–21.00 WITA',
                'deskripsi' => 'UMKM asal Barabai yang menyajikan aneka cemilan mulai 5 ribuan, kue kering, dan frozen food rumahan. Enak, terjangkau, dan bikin nagih.',
                'lokasi_lat' => -2.5905603,
                'lokasi_lng' => 115.361494,
            ]);
    }

    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'instagram', 'jam_buka', 'deskripsi', 'logo', 'lokasi_lat', 'lokasi_lng']);
        });
    }
};
