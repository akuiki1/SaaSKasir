<?php

use App\Models\Toko;
use App\Models\Transaksi;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Peran platform (ceo & superadmin): akses panel, isolasi dari rute toko,
| dan aksi kelola superadmin (status toko, langganan, onboarding, reset
| password admin). Admin toko TIDAK boleh masuk panel platform — superset
| admin hanya berlaku atas peran kasir (keputusan 2026-07-05, dipersempit
| 2026-07-07).
|--------------------------------------------------------------------------
*/

/** User platform: tanpa toko (id_toko null) — factory di-override eksplisit. */
function platformUser(string $role): User
{
    return User::factory()->create(['role' => $role, 'id_toko' => null]);
}

// ─── Redirect dashboard per peran ────────────────────────────────────────────

test('ceo diarahkan ke dashboard ceo', function () {
    $this->actingAs(platformUser('ceo'));

    $this->get(route('dashboard'))->assertRedirect(route('ceo.dashboard'));
});

test('superadmin diarahkan ke dashboard superadmin', function () {
    $this->actingAs(platformUser('superadmin'));

    $this->get(route('dashboard'))->assertRedirect(route('superadmin.dashboard'));
});

// ─── Kontrol akses antar peran ───────────────────────────────────────────────

test('ceo bisa buka dashboardnya tapi tidak panel superadmin maupun rute toko', function () {
    $this->actingAs(platformUser('ceo'));

    $this->get(route('ceo.dashboard'))->assertOk();
    $this->get(route('superadmin.dashboard'))->assertForbidden();
    $this->get(route('superadmin.toko'))->assertForbidden();
    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->get(route('kasir.dashboard'))->assertForbidden();
});

test('superadmin bisa buka panel kelola tapi tidak dashboard ceo maupun rute toko', function () {
    $this->actingAs(platformUser('superadmin'));

    $this->get(route('superadmin.dashboard'))->assertOk();
    $this->get(route('superadmin.toko'))->assertOk();
    $this->get(route('superadmin.admins'))->assertOk();
    $this->get(route('ceo.dashboard'))->assertForbidden();
    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('admin toko tetap superset kasir tapi ditolak dari panel platform', function () {
    Toko::factory()->create();
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get(route('kasir.dashboard'))->assertOk();
    $this->get(route('ceo.dashboard'))->assertForbidden();
    $this->get(route('superadmin.dashboard'))->assertForbidden();
});

test('kasir ditolak dari semua panel platform', function () {
    Toko::factory()->create();
    $this->actingAs(User::factory()->create(['role' => 'kasir']));

    $this->get(route('ceo.dashboard'))->assertForbidden();
    $this->get(route('superadmin.toko'))->assertForbidden();
});

// ─── Dashboard CEO: agregat lintas-tenant ────────────────────────────────────

test('dashboard ceo menghitung GMV & toko dari SEMUA tenant', function () {
    $tenant = app(TenantContext::class);

    $tokoA = Toko::factory()->create();
    $tenant->setToko($tokoA);
    $kasirA = User::factory()->create(['role' => 'kasir']);
    Transaksi::factory()->create(['id_user' => $kasirA->id, 'total_harga' => 10000]);

    $tokoB = Toko::factory()->create();
    $tenant->setToko($tokoB);
    $kasirB = User::factory()->create(['role' => 'kasir', 'id_toko' => $tokoB->id_toko]);
    Transaksi::factory()->create(['id_user' => $kasirB->id, 'id_toko' => $tokoB->id_toko, 'total_harga' => 25000]);

    // Buang memo TenantContext hasil seeding di atas supaya request ceo
    // me-resolve ulang dari user login (jebakan singleton — lihat memori tim).
    app()->forgetInstance(TenantContext::class);

    $this->actingAs(platformUser('ceo'));

    // Total toko dihitung dinamis: migration create_tokos_table menyisipkan
    // satu toko default (backfill multi-tenant), jadi bukan pasti 2.
    $totalToko = Toko::count();

    $this->get(route('ceo.dashboard'))->assertOk()->assertInertia(
        fn ($page) => $page
            ->component('ceo/Dashboard')
            ->where('toko_stats.total', $totalToko)
            ->where('gmv_stats.hari_ini.gmv', 35000)
            ->where('gmv_stats.hari_ini.transaksi', 2),
    );
});

// ─── Aksi superadmin ─────────────────────────────────────────────────────────

test('superadmin bisa menonaktifkan dan mengaktifkan toko', function () {
    $toko = Toko::factory()->create();
    $this->actingAs(platformUser('superadmin'));

    $this->put(route('superadmin.toko.status', $toko), ['status' => 'nonaktif'])
        ->assertRedirect();
    expect($toko->fresh()->status)->toBe('nonaktif');

    $this->put(route('superadmin.toko.status', $toko), ['status' => 'aktif'])
        ->assertRedirect();
    expect($toko->fresh()->status)->toBe('aktif');
});

test('superadmin bisa mencatat perpanjangan langganan toko', function () {
    $toko = Toko::factory()->create();
    $this->actingAs(platformUser('superadmin'));

    $this->post(route('superadmin.toko.langganan', $toko), [
        'tier' => 'warung',
        'bulan' => 2,
    ])->assertRedirect();

    $toko->refresh();
    expect($toko->tierEfektif())->toBe('warung');
    $this->assertDatabaseHas('langganan_pembayarans', [
        'id_toko' => $toko->id_toko,
        'tier' => 'warung',
        'metode' => 'manual',
    ]);
});

test('perpanjangan langganan menolak tier gratis', function () {
    $toko = Toko::factory()->create();
    $this->actingAs(platformUser('superadmin'));

    $this->post(route('superadmin.toko.langganan', $toko), [
        'tier' => 'gratis',
        'bulan' => 1,
    ])->assertSessionHasErrors('tier');
});

test('superadmin bisa mendaftarkan toko baru beserta admin pemiliknya', function () {
    $this->actingAs(platformUser('superadmin'));

    $this->post(route('superadmin.toko.store'), [
        'nama_toko' => 'Warung Uji Platform',
        'nama_pemilik' => 'Pak Uji',
        'email' => 'pakuji@example.com',
        'whatsapp' => '081234567890',
        'password' => 'rahasia-123',
        'password_confirmation' => 'rahasia-123',
    ])->assertRedirect(route('superadmin.toko'));

    $toko = Toko::where('nama', 'Warung Uji Platform')->first();
    expect($toko)->not->toBeNull();

    $admin = User::where('email', 'pakuji@example.com')->first();
    expect($admin->role)->toBe('admin');
    expect($admin->id_toko)->toBe($toko->id_toko);
});

test('superadmin bisa mereset password admin, tapi bukan akun kasir', function () {
    Toko::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $kasir = User::factory()->create(['role' => 'kasir']);
    $this->actingAs(platformUser('superadmin'));

    $this->put(route('superadmin.admins.password', $admin), [
        'password' => 'password-baru-1',
        'password_confirmation' => 'password-baru-1',
    ])->assertRedirect();

    expect(Hash::check('password-baru-1', $admin->fresh()->password))->toBeTrue();

    // Target non-admin dibalas 404 (jangan bocorkan keberadaan ID).
    $this->put(route('superadmin.admins.password', $kasir), [
        'password' => 'password-baru-1',
        'password_confirmation' => 'password-baru-1',
    ])->assertNotFound();
});
