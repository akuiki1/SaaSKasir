<?php

namespace Database\Seeders;

use App\Models\Toko;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // User tidak memakai trait BelongsToToko (lihat catatan di model), dan
        // id_toko sengaja tidak fillable (bukan mass-assignable) — forceCreate
        // dipakai di sini karena ini kode server tepercaya, bukan input pengguna.
        $idToko = Toko::query()->value('id_toko');

        User::forceCreate([
            'name' => 'Budi Santoso',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'id_toko' => $idToko,
            'password' => Hash::make('admin123'),
        ]);

        $kasirs = [
            ['name' => 'Siti Rahayu',    'email' => 'siti@gmail.com'],
            ['name' => 'Agus Permana',   'email' => 'agus@gmail.com'],
            ['name' => 'Dewi Lestari',   'email' => 'dewi@gmail.com'],
        ];

        foreach ($kasirs as $kasir) {
            User::forceCreate([
                'name' => $kasir['name'],
                'email' => $kasir['email'],
                'role' => 'kasir',
                'id_toko' => $idToko,
                'password' => Hash::make('kasir123'),
            ]);
        }
    }
}
