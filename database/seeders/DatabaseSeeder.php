<?php

namespace Database\Seeders;

use App\Models\Toko;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Toko default untuk data contoh/dev. Semua seeder di bawah membuat
        // model lewat trait BelongsToToko, jadi cukup set TenantContext sekali
        // di sini — id_toko terisi otomatis di tiap baris yang dibuat.
        $toko = Toko::query()->firstOrCreate(
            ['slug' => 'cemilan-mba-tutut'],
            ['nama' => 'Cemilan Mba Tutut', 'status' => 'aktif'],
        );

        app(TenantContext::class)->set($toko->id_toko);

        $this->call([
            UserSeeder::class,
            KategoriSeeder::class,
            ProdukSeeder::class,
            ProduksiSeeder::class,
            PromoSeeder::class,
            PengeluaranSeeder::class,
            TransaksiSeeder::class,
        ]);
    }
}
