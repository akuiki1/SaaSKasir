<?php

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Toko;
use App\Models\User;

test('unauthenticated request to the produk catalog is rejected', function () {
    $this->getJson('/api/produk')->assertStatus(401);
});

test('an authenticated token can fetch the produk catalog', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $kategori = Kategori::factory()->create(['nama_kategori' => 'Minuman']);
    $produk = Produk::factory()->create([
        'id_kategori' => $kategori->id_kategori,
        'nama' => 'Kopi Susu',
        'tipe_jual' => 'satuan',
        'harga_jual' => 12000,
        'stok' => 25,
        'barcode' => '1234567890123',
    ]);

    $token = $kasir->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/produk');

    $response->assertOk()->assertJson([
        'produks' => [
            [
                'id_produk' => $produk->id_produk,
                'nama' => 'Kopi Susu',
                'kategori' => 'Minuman',
                'tipe_jual' => 'satuan',
                'harga_jual' => 12000,
                'stok' => 25,
                'barcode' => '1234567890123',
            ],
        ],
    ]);
});

test('the produk catalog excludes jasa products', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    Produk::factory()->create(['tipe_jual' => 'satuan']);
    Produk::factory()->create(['tipe_jual' => 'jasa']);

    $token = $kasir->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/produk');

    $response->assertOk();
    expect($response->json('produks'))->toHaveCount(1);
});

test('the produk catalog only returns products belonging to the caller\'s toko', function () {
    $tokoLain = Toko::factory()->create();
    Produk::factory()->create(['id_toko' => $tokoLain->id_toko, 'nama' => 'Produk Toko Lain']);

    $kasir = User::factory()->create(['role' => 'kasir']);
    $produkSendiri = Produk::factory()->create(['id_toko' => $kasir->id_toko, 'nama' => 'Produk Toko Sendiri']);

    $token = $kasir->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/produk');

    $response->assertOk();
    $nama = collect($response->json('produks'))->pluck('nama');

    expect($nama)->toContain('Produk Toko Sendiri');
    expect($nama)->not->toContain('Produk Toko Lain');
});
