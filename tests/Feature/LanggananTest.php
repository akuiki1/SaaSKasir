<?php

use App\Models\Toko;
use App\Models\User;
use App\Services\LanggananService;
use App\Support\TenantContext;
use Illuminate\Support\Carbon;

/*
|--------------------------------------------------------------------------
| Langganan: tier efektif, pencatatan pembayaran (service+command), dan
| feature-gating Laporan Keuangan (Laba Rugi) di balik paywall Warung+.
|--------------------------------------------------------------------------
*/

/** Set tier & masa berlaku langsung (bukan fillable — pakai forceFill). */
function setTier(Toko $toko, string $tier, ?string $sampai): Toko
{
    $toko->forceFill(['tier' => $tier, 'langganan_sampai' => $sampai])->save();

    return $toko->fresh();
}

// ─── tierEfektif ─────────────────────────────────────────────────────────────

test('tier gratis selalu efektif gratis', function () {
    $toko = setTier(Toko::factory()->create(), 'gratis', null);
    expect($toko->tierEfektif())->toBe('gratis');
});

test('tier berbayar tanpa tanggal = perpetual (grandfather)', function () {
    $toko = setTier(Toko::factory()->create(), 'bisnis', null);
    expect($toko->tierEfektif())->toBe('bisnis');
});

test('tier berbayar dengan tanggal di masa depan tetap aktif', function () {
    $toko = setTier(Toko::factory()->create(), 'warung', Carbon::today()->addMonth()->toDateString());
    expect($toko->tierEfektif())->toBe('warung');
});

test('tier berbayar yang kedaluwarsa turun ke gratis', function () {
    $toko = setTier(Toko::factory()->create(), 'warung', Carbon::yesterday()->toDateString());
    expect($toko->tierEfektif())->toBe('gratis');
});

// ─── LanggananService ────────────────────────────────────────────────────────

test('pembayaran dari gratis mulai hari ini', function () {
    $toko = Toko::factory()->create();

    $bayar = app(LanggananService::class)->catatPembayaran($toko, 'warung', 1);

    expect($bayar->periode_mulai->toDateString())->toBe(Carbon::today()->toDateString());
    expect($bayar->periode_sampai->toDateString())->toBe(Carbon::today()->addMonth()->toDateString());
    expect($toko->fresh()->tier)->toBe('warung');
    expect($toko->fresh()->langganan_sampai->toDateString())->toBe(Carbon::today()->addMonth()->toDateString());

    $this->assertDatabaseHas('langganan_pembayarans', [
        'id_toko' => $toko->id_toko,
        'tier' => 'warung',
        'metode' => 'manual',
    ]);
});

test('pembayaran menumpuk dari akhir periode bila masih aktif', function () {
    $toko = Toko::factory()->create();
    $service = app(LanggananService::class);

    $service->catatPembayaran($toko, 'warung', 1); // s/d hari ini + 1 bulan
    $service->catatPembayaran($toko->fresh(), 'warung', 1); // menumpuk

    expect($toko->fresh()->langganan_sampai->toDateString())
        ->toBe(Carbon::today()->addMonths(2)->toDateString());
    $this->assertDatabaseCount('langganan_pembayarans', 2);
});

test('nominal default diambil dari harga tier x bulan', function () {
    $toko = Toko::factory()->create();

    $bayar = app(LanggananService::class)->catatPembayaran($toko, 'warung', 2);

    // config warung = 99000 x 2 bulan.
    expect($bayar->nominal)->toBe(198000);
});

// ─── Feature-gating Laporan Keuangan ─────────────────────────────────────────

test('admin toko gratis dialihkan dari laporan keuangan ke halaman langganan', function () {
    $toko = setTier(Toko::factory()->create(), 'gratis', null);
    $admin = User::factory()->create(['role' => 'admin', 'id_toko' => $toko->id_toko]);

    app()->forgetInstance(TenantContext::class);

    $this->actingAs($admin)
        ->get(route('admin.laporan.keuangan'))
        ->assertRedirect(route('admin.langganan'));
});

test('admin toko warung boleh membuka laporan keuangan', function () {
    $toko = setTier(Toko::factory()->create(), 'warung', Carbon::today()->addMonth()->toDateString());
    $admin = User::factory()->create(['role' => 'admin', 'id_toko' => $toko->id_toko]);

    app()->forgetInstance(TenantContext::class);

    $this->actingAs($admin)
        ->get(route('admin.laporan.keuangan'))
        ->assertOk();
});

test('laporan lain tidak dikunci untuk toko gratis', function () {
    $toko = setTier(Toko::factory()->create(), 'gratis', null);
    $admin = User::factory()->create(['role' => 'admin', 'id_toko' => $toko->id_toko]);

    app()->forgetInstance(TenantContext::class);

    foreach (['penjualan', 'inventaris', 'pelanggan'] as $laporan) {
        $this->actingAs($admin)
            ->get(route("admin.laporan.{$laporan}"))
            ->assertOk();
    }
});

// ─── Share Inertia ───────────────────────────────────────────────────────────

test('share langganan menandai laporan_keuangan sesuai tier', function () {
    $tokoGratis = setTier(Toko::factory()->create(), 'gratis', null);
    $adminGratis = User::factory()->create(['role' => 'admin', 'id_toko' => $tokoGratis->id_toko]);

    app()->forgetInstance(TenantContext::class);
    $this->actingAs($adminGratis)
        ->get(route('admin.langganan'))
        ->assertInertia(fn ($page) => $page->where('langganan.tier', 'gratis')
            ->where('langganan.fitur.laporan_keuangan', false));

    $tokoBisnis = setTier(Toko::factory()->create(), 'bisnis', null);
    $adminBisnis = User::factory()->create(['role' => 'admin', 'id_toko' => $tokoBisnis->id_toko]);

    app()->forgetInstance(TenantContext::class);
    $this->actingAs($adminBisnis)
        ->get(route('admin.langganan'))
        ->assertInertia(fn ($page) => $page->where('langganan.tier', 'bisnis')
            ->where('langganan.fitur.laporan_keuangan', true));
});

// ─── Command ─────────────────────────────────────────────────────────────────

test('command langganan:catat menandai toko sudah bayar', function () {
    $toko = Toko::factory()->create(['slug' => 'toko-uji-command']);

    $this->artisan('langganan:catat', ['toko' => 'toko-uji-command', 'tier' => 'warung', '--bulan' => 3])
        ->assertSuccessful();

    expect($toko->fresh()->tier)->toBe('warung');
    expect($toko->fresh()->langganan_sampai->toDateString())
        ->toBe(Carbon::today()->addMonths(3)->toDateString());
});

test('command menolak tier tidak valid', function () {
    $toko = Toko::factory()->create(['slug' => 'toko-uji-invalid']);

    $this->artisan('langganan:catat', ['toko' => 'toko-uji-invalid', 'tier' => 'premium'])
        ->assertFailed();

    expect($toko->fresh()->tier)->toBe('gratis');
});

// ─── Isolasi tenant ──────────────────────────────────────────────────────────

test('pembayaran ter-scope ke tokonya sendiri', function () {
    $tokoA = Toko::factory()->create();
    $tokoB = Toko::factory()->create();
    $service = app(LanggananService::class);

    $service->catatPembayaran($tokoA, 'warung', 1);
    $service->catatPembayaran($tokoB, 'bisnis', 1);

    expect($tokoA->pembayarans()->count())->toBe(1);
    expect($tokoA->pembayarans()->first()->tier)->toBe('warung');
    expect($tokoB->pembayarans()->first()->tier)->toBe('bisnis');
});
