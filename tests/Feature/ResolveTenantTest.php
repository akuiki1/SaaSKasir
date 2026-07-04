<?php

use App\Models\Produk;
use App\Models\Toko;
use App\Support\TenantContext;

/*
|--------------------------------------------------------------------------
| Resolusi tenant untuk rute publik (storefront, pesan online).
|--------------------------------------------------------------------------
*/

test('storefront slug route resolves the matching toko and its products', function () {
    $tokoA = Toko::factory()->create(['nama' => 'Toko A', 'slug' => 'toko-a', 'whatsapp' => '6281111111111']);
    $tokoB = Toko::factory()->create(['nama' => 'Toko B', 'slug' => 'toko-b', 'whatsapp' => '6282222222222']);

    app(TenantContext::class)->setToko($tokoA);
    $produkA = Produk::factory()->create(['nama' => 'Produk A', 'tipe_jual' => 'satuan']);

    app(TenantContext::class)->setToko($tokoB);
    $produkB = Produk::factory()->create(['nama' => 'Produk B', 'tipe_jual' => 'satuan']);

    $this->get(route('toko.home', ['tokoSlug' => 'toko-a']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('allProducts.0.nama', 'Produk A')
        );

    $this->get(route('toko.home', ['tokoSlug' => 'toko-b']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('allProducts.0.nama', 'Produk B')
        );
});

test('storefront slug route 404s for an unknown or nonaktif toko', function () {
    $this->get(route('toko.home', ['tokoSlug' => 'tidak-ada']))->assertNotFound();

    Toko::factory()->create(['slug' => 'toko-tutup', 'status' => 'nonaktif']);
    $this->get(route('toko.home', ['tokoSlug' => 'toko-tutup']))->assertNotFound();
});

test('web order confirmation whatsapp link points to the resolved toko, not another toko', function () {
    $tokoA = Toko::factory()->create(['nama' => 'Toko A', 'slug' => 'toko-a', 'whatsapp' => '6281111111111']);
    Toko::factory()->create(['nama' => 'Toko B', 'slug' => 'toko-b', 'whatsapp' => '6282222222222']);

    app(TenantContext::class)->setToko($tokoA);
    $produk = Produk::factory()->create(['tipe_jual' => 'satuan', 'harga_jual' => 10000, 'stok' => 10]);

    $response = $this->postJson(route('toko.pesan.store', ['tokoSlug' => 'toko-a']), [
        'nama' => 'Budi',
        'telp' => '081298765432',
        'items' => [
            ['id_produk' => $produk->id_produk, 'jumlah' => 1],
        ],
    ])->assertOk();

    expect($response->json('wa_url'))
        ->toContain('6281111111111')
        ->not->toContain('6282222222222');
});
