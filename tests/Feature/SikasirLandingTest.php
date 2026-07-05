<?php

use App\Models\Toko;

/*
|--------------------------------------------------------------------------
| Landing page publik SiKasir-the-product di rute akar `/` — pintu depan SaaS
| (halaman marketing), BUKAN storefront toko. Di luar middleware tenant.
| Storefront toko kini hanya via /toko/{slug}.
|--------------------------------------------------------------------------
*/

test('the root route serves the SiKasir product landing', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Landing'));
});

test('root landing renders regardless of tenant state (outside tenant middleware)', function () {
    // Nonaktifkan semua toko: kalau `/` masih lewat ResolveTenant, firstOrFail
    // atas "toko aktif" akan 404. Landing produk harus tetap tampil karena
    // berada DI LUAR middleware tenant.
    Toko::query()->update(['status' => 'nonaktif']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Landing'));
});

test('landing exposes subscription tiers sourced from config', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Landing')
            ->has('tiers', count(config('langganan.tiers')))
            ->where('tiers.0.key', 'gratis')
            ->where('tiers.0.harga', 0)
            ->where('tiers.1.harga', (int) config('langganan.tiers.warung.harga'))
        );
});

test('the storefront is still reachable via its slug, not the root', function () {
    Toko::factory()->create(['slug' => 'toko-demo', 'status' => 'aktif']);

    // Root = landing produk; storefront toko yang sebenarnya hidup di slug-nya.
    $this->get('/')
        ->assertInertia(fn ($page) => $page->component('Landing'));

    $this->get(route('toko.home', ['tokoSlug' => 'toko-demo']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Welcome'));
});
