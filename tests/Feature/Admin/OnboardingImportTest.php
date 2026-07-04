<?php

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/*
|--------------------------------------------------------------------------
| Impor produk massal (CSV/Excel) saat onboarding -- delegasi ke
| ProdukService::buat() supaya aturan bisnis konsisten dengan tambah manual.
|--------------------------------------------------------------------------
*/

function fileCsv(string $isi): UploadedFile
{
    return UploadedFile::fake()->createWithContent('produk.csv', $isi);
}

test('valid csv import creates products with the correct fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $csv = "nama,kategori,harga_jual,stok,barcode\n"
        ."Kopi Sachet,Minuman,3000,50,\n"
        ."Indomie Goreng,Makanan,3500,100,\n";

    $response = $this->actingAs($admin)->post(route('admin.onboarding.import'), [
        'file' => fileCsv($csv),
    ]);

    $response->assertRedirect(route('admin.onboarding'));
    $response->assertSessionHas('success');

    $this->assertDatabaseCount('produks', 2);
    $this->assertDatabaseHas('produks', [
        'nama' => 'Kopi Sachet',
        'harga_jual' => 3000,
        'stok' => 50,
        'tipe_jual' => 'satuan',
    ]);

    $kategoriMinuman = Kategori::where('nama_kategori', 'Minuman')->first();
    expect($kategoriMinuman)->not->toBeNull();
    expect(Produk::where('nama', 'Kopi Sachet')->first()->id_kategori)->toBe($kategoriMinuman->id_kategori);

    // Barcode kosong di CSV -> TETAP kosong (ProdukService::buat() tidak auto-generate barcode,
    // sama seperti tambah produk manual satu-satu; "Generate Barcode Semua" adalah aksi terpisah).
    expect(Produk::where('nama', 'Kopi Sachet')->first()->barcode)->toBeNull();
});

test('invalid rows are skipped with a reason while valid rows still import', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $csv = "nama,kategori,harga_jual,stok,barcode\n"
        ."Kopi Sachet,Minuman,3000,50,\n"
        .",Makanan,3500,100,\n" // nama kosong -> harus dilewati
        ."Teh Botol,Minuman,-500,10,\n"; // harga negatif -> harus dilewati

    $response = $this->actingAs($admin)->post(route('admin.onboarding.import'), [
        'file' => fileCsv($csv),
    ]);

    $response->assertRedirect(route('admin.onboarding'));

    $this->assertDatabaseCount('produks', 1);
    $this->assertDatabaseHas('produks', ['nama' => 'Kopi Sachet']);
});

test('category matching is case-insensitive and does not create duplicates', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Kategori::factory()->create(['nama_kategori' => 'Minuman']);

    $csv = "nama,kategori,harga_jual,stok,barcode\n"
        ."Kopi Sachet,minuman,3000,50,\n"; // huruf kecil semua, harus cocok ke "Minuman"

    $this->actingAs($admin)->post(route('admin.onboarding.import'), [
        'file' => fileCsv($csv),
    ]);

    $this->assertDatabaseCount('kategoris', 1);
});

test('guests and kasir cannot access the onboarding import route', function () {
    $this->post(route('admin.onboarding.import'), ['file' => fileCsv('nama,kategori,harga_jual,stok,barcode')])
        ->assertRedirect(route('login'));

    $kasir = User::factory()->create(['role' => 'kasir']);
    $this->actingAs($kasir)
        ->post(route('admin.onboarding.import'), ['file' => fileCsv('nama,kategori,harga_jual,stok,barcode')])
        ->assertForbidden();
});

test('imported products are scoped to the importing admin\'s own toko', function () {
    $tokoLain = Toko::factory()->create();
    Produk::factory()->create(['id_toko' => $tokoLain->id_toko, 'nama' => 'Produk Toko Lain']);

    // Toko sendiri eksplisit (bukan fallback default UserFactory) supaya test ini
    // benar-benar menguji resolusi via Auth::user() saat request, bukan kebetulan
    // sama-sama jatuh ke fallback "toko pertama di DB".
    $tokoSendiri = Toko::factory()->create();
    $admin = User::factory()->create(['role' => 'admin', 'id_toko' => $tokoSendiri->id_toko]);
    $csv = "nama,kategori,harga_jual,stok,barcode\n"
        ."Kopi Sachet,Minuman,3000,50,\n";

    // TenantContext singleton bisa ter-memoize dini oleh query Produk::factory()
    // di atas (sebelum ada user login) -- reset supaya request import di bawah
    // benar-benar resolve dari Auth::user() (admin) yang sesungguhnya.
    app()->forgetInstance(\App\Support\TenantContext::class);

    $this->actingAs($admin)->post(route('admin.onboarding.import'), [
        'file' => fileCsv($csv),
    ]);

    $produkBaru = Produk::where('nama', 'Kopi Sachet')->first();
    expect($produkBaru->id_toko)->toBe($tokoSendiri->id_toko);
    expect($produkBaru->id_toko)->not->toBe($tokoLain->id_toko);
});
