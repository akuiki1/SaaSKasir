<?php

namespace App\Services;

use App\Models\Toko;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Registrasi mandiri: buat Toko + User admin pertamanya sekaligus. Dipakai
 * oleh App\Actions\Fortify\CreateNewUser (jalur registrasi publik).
 */
class RegistrasiService
{
    /**
     * @param  array{nama_toko: string, nama_pemilik: string, email: string, whatsapp: string, password: string}  $data
     */
    public function daftarkanToko(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $toko = Toko::create([
                'nama' => $data['nama_toko'],
                'slug' => $this->buatSlugUnik($data['nama_toko']),
                'whatsapp' => $data['whatsapp'],
                'status' => 'aktif',
            ]);

            // forceCreate: id_toko sengaja tidak fillable (lihat User model),
            // dan verifikasi email dilewati (email_verified_at langsung terisi)
            // supaya onboarding tidak terhenti menunggu email yang belum tentu
            // terkonfigurasi (lihat catatan di rencana registrasi mandiri).
            return User::forceCreate([
                'name' => $data['nama_pemilik'],
                'email' => $data['email'],
                'role' => 'admin',
                'id_toko' => $toko->id_toko,
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);
        });
    }

    private function buatSlugUnik(string $namaToko): string
    {
        $dasar = Str::slug($namaToko);
        $slug = $dasar;
        $n = 2;

        while (Toko::where('slug', $slug)->exists()) {
            $slug = "{$dasar}-{$n}";
            $n++;
        }

        return $slug;
    }
}
