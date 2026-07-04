<?php

use App\Models\Produk;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API-first: login berbasis token (Sanctum) + endpoint transaksi pertama,
| memakai ulang KasirService yang sama persis dengan halaman kasir web.
|--------------------------------------------------------------------------
*/

test('a user can log in via the API and receive a bearer token', function () {
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'rahasia123',
        'device_name' => 'test-device',
    ]);

    $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'name', 'role']]);
});

test('login fails with wrong credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'salah',
        'device_name' => 'test-device',
    ])->assertStatus(422);
});

test('unauthenticated request to a protected API route is rejected', function () {
    $this->postJson('/api/transaksi', [])->assertStatus(401);
});

test('an authenticated token can create a sale via the API using KasirService', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $produk = Produk::factory()->create([
        'tipe_jual' => 'satuan',
        'harga_jual' => 15000,
        'stok' => 10,
    ]);

    $token = $kasir->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/transaksi', [
            'metode_pembayaran' => 'cash',
            'bayar' => 30000,
            'items' => [
                ['id_produk' => $produk->id_produk, 'jumlah' => 2],
            ],
        ]);

    $response->assertCreated()->assertJson([
        'total_harga' => 30000,
        'bayar' => 30000,
        'kembalian' => 0,
    ]);

    $this->assertDatabaseHas('transaksis', [
        'id_user' => $kasir->id,
        'total_harga' => 30000,
    ]);
    expect((float) $produk->fresh()->stok)->toBe(8.0);
});

test('the API sale endpoint validates stock via the same rules as the web cashier', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $produk = Produk::factory()->create(['tipe_jual' => 'satuan', 'harga_jual' => 10000, 'stok' => 1]);

    $token = $kasir->createToken('test-device')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/transaksi', [
            'metode_pembayaran' => 'cash',
            'bayar' => 50000,
            'items' => [
                ['id_produk' => $produk->id_produk, 'jumlah' => 5],
            ],
        ])->assertStatus(422);
});
