<?php

use App\Models\User;

// Jembatan flash sesi klasik (`redirect()->with('success'/'error', ...)`) →
// toast Inertia (`page.flash.toast`, dikonsumsi flashToast.ts). Lihat
// HandleInertiaRequests::bridgeSessionFlashToToast.

test('session success flash bridges to inertia flash toast', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withSession(['success' => 'Kategori berhasil ditambahkan.'])
        ->get(route('admin.kategori'));

    $response->assertOk();
    $page = $response->viewData('page');

    expect($page['flash']['toast'] ?? null)->toEqual([
        'type' => 'success',
        'message' => 'Kategori berhasil ditambahkan.',
    ]);
});

test('session error flash bridges to inertia flash toast', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withSession(['error' => 'Fitur ini memerlukan langganan berbayar.'])
        ->get(route('admin.langganan'));

    $response->assertOk();
    $page = $response->viewData('page');

    expect($page['flash']['toast'] ?? null)->toEqual([
        'type' => 'error',
        'message' => 'Fitur ini memerlukan langganan berbayar.',
    ]);
});

test('paywall redirect surfaces its message as an error toast', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Toko default test di-grandfather ke 'bisnis' oleh migrasi langganan —
    // turunkan ke gratis dulu supaya laporan keuangan terkunci (tier non-fillable).
    \App\Models\Toko::query()->firstOrFail()
        ->forceFill(['tier' => 'gratis', 'langganan_sampai' => null])
        ->save();

    $redirect = $this->actingAs($admin)->get(route('admin.laporan.keuangan'));

    $redirect->assertRedirect(route('admin.langganan'));

    // Ikuti redirect: pesan paywall harus tampil sebagai toast di halaman langganan.
    $page = $this->actingAs($admin)
        ->get(route('admin.langganan'))
        ->viewData('page');

    expect($page['flash']['toast']['type'] ?? null)->toBe('error');
    expect($page['flash']['toast']['message'] ?? '')->toContain('langganan berbayar');
});

test('explicit inertia toast is not overridden by the session bridge', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // PesananController memakai Inertia::flash('toast', ...) langsung — controller
    // berjalan SETELAH share(), jadi toast eksplisit harus menang atas jembatan.
    $produk = \App\Models\Produk::factory()->create([
        'harga_jual' => 10000,
        'stok' => 10,
        'tipe_jual' => 'satuan',
    ]);

    $pesanan = \App\Models\Pesanan::create([
        'nama_pelanggan' => 'Budi',
        'telp' => '6281298765432',
        'tipe_pelanggan' => 'umum',
        'status' => 'pending',
        'total' => 10000,
        'sumber' => 'web',
    ]);

    $pesanan->items()->create([
        'id_produk' => $produk->id_produk,
        'nama_produk' => $produk->nama,
        'harga' => 10000,
        'jumlah' => 1,
        'subtotal' => 10000,
    ]);

    $this->actingAs($admin)
        ->withSession(['success' => 'Pesan lama yang seharusnya kalah.'])
        ->post(route('admin.pesanan.siap', $pesanan));

    $page = $this->actingAs($admin)
        ->get(route('admin.pesanan'))
        ->viewData('page');

    expect($page['flash']['toast']['message'] ?? '')
        ->toContain($pesanan->kode)
        ->not->toContain('seharusnya kalah');
});

test('pages without session flash carry no toast', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $page = $this->actingAs($admin)
        ->get(route('admin.kategori'))
        ->viewData('page');

    expect($page['flash']['toast'] ?? null)->toBeNull();
});
