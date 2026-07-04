<?php

use App\Models\Toko;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Registrasi mandiri: buat Toko + User admin sekaligus, auto-login, langsung
| ke onboarding tanpa terhambat verifikasi email.
|--------------------------------------------------------------------------
*/

function dataRegistrasi(array $override = []): array
{
    return array_merge([
        'nama_toko' => 'Warung Berkah Jaya',
        'nama_pemilik' => 'Siti Aminah',
        'email' => 'siti@contoh.com',
        'whatsapp' => '081234567890',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ], $override);
}

test('registering creates a toko and admin user, then redirects to onboarding', function () {
    $response = $this->post('/register', dataRegistrasi());

    $response->assertRedirect(route('admin.onboarding'));

    $toko = Toko::where('nama', 'Warung Berkah Jaya')->first();
    expect($toko)->not->toBeNull();
    expect($toko->slug)->toBe('warung-berkah-jaya');
    expect($toko->status)->toBe('aktif');
    expect($toko->whatsapp)->toBe('081234567890');

    $user = User::where('email', 'siti@contoh.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role)->toBe('admin');
    expect($user->id_toko)->toBe($toko->id_toko);
    expect($user->name)->toBe('Siti Aminah');
    // Verifikasi email dilewati -- onboarding tidak boleh terhambat menunggu email.
    expect($user->email_verified_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('registering a second toko with the same name gets a disambiguated slug', function () {
    $this->post('/register', dataRegistrasi(['email' => 'pertama@contoh.com']));
    // Registrasi pertama otomatis login -- logout dulu supaya post kedua tidak
    // dibentengi middleware guest (yang akan menolaknya sebelum sampai controller).
    $this->post('/logout');
    $this->post('/register', dataRegistrasi(['email' => 'kedua@contoh.com']));

    $slugs = Toko::where('nama', 'Warung Berkah Jaya')->pluck('slug')->sort()->values();

    expect($slugs->all())->toBe(['warung-berkah-jaya', 'warung-berkah-jaya-2']);
});

test('registration rejects a duplicate email', function () {
    // Baseline 1 toko ("Cemilan Mba Tutut") sudah ada dari migrasi create_tokos_table.
    $this->assertDatabaseCount('tokos', 1);
    User::factory()->create(['email' => 'siti@contoh.com']);

    $response = $this->from('/register')->post('/register', dataRegistrasi());

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseCount('tokos', 1);
});

test('registration rejects mismatched password confirmation', function () {
    $response = $this->from('/register')->post('/register', dataRegistrasi([
        'password_confirmation' => 'beda-sekali',
    ]));

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseCount('tokos', 1); // baseline "Cemilan Mba Tutut", tidak nambah
    $this->assertDatabaseCount('users', 0);
});

test('registration rejects an invalid whatsapp number', function () {
    $response = $this->from('/register')->post('/register', dataRegistrasi([
        'whatsapp' => 'abc',
    ]));

    $response->assertSessionHasErrors('whatsapp');
    $this->assertDatabaseCount('tokos', 1); // baseline "Cemilan Mba Tutut", tidak nambah
});
