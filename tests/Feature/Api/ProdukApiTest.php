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

    // Toko sendiri dibuat eksplisit (bukan mengandalkan fallback default UserFactory
    // ke "toko pertama di DB tanpa ORDER BY") -- begitu 2+ toko ada, fallback itu
    // ambigu dan bisa kebetulan me-resolve ke $tokoLain, membuat test ini flaky.
    $tokoSendiri = Toko::factory()->create();
    $kasir = User::factory()->create(['role' => 'kasir', 'id_toko' => $tokoSendiri->id_toko]);
    $produkSendiri = Produk::factory()->create(['id_toko' => $tokoSendiri->id_toko, 'nama' => 'Produk Toko Sendiri']);

    $token = $kasir->createToken('test-device')->plainTextToken;

    // TenantContext adalah singleton yang memoize resolusi sekali per proses
    // (lihat AppServiceProvider). Query Produk::factory() di atas (sebelum ada
    // user login) sudah memicu resolusi dini via fallback "toko pertama di DB" --
    // reset instance-nya di sini supaya request API di bawah me-resolve ulang
    // dari Auth::user() (kasir) yang sesungguhnya, bukan nilai basi.
    app()->forgetInstance(\App\Support\TenantContext::class);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/produk');

    $response->assertOk();
    $nama = collect($response->json('produks'))->pluck('nama');

    expect($nama)->toContain('Produk Toko Sendiri');
    expect($nama)->not->toContain('Produk Toko Lain');
});
