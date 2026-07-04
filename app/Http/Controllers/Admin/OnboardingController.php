<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Services\ProdukImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Onboarding sekali-jalan setelah registrasi mandiri: unduh template produk,
 * impor massal (CSV/Excel), atau langsung lewati ke kasir -- tidak memaksa
 * alur linear, supaya toko yang cuma punya sedikit produk tetap bisa cepat
 * sampai ke transaksi pertama.
 */
class OnboardingController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('admin/Onboarding', [
            'jumlah_produk' => Produk::count(),
        ]);
    }

    /**
     * Unduh template CSV kolom produk (nama, kategori, harga_jual, stok, barcode).
     */
    public function template(): HttpResponse
    {
        $baris = [
            ['nama', 'kategori', 'harga_jual', 'stok', 'barcode'],
            ['Contoh: Kopi Sachet', 'Minuman', '3000', '50', ''],
            ['Contoh: Indomie Goreng', 'Makanan', '3500', '100', ''],
        ];

        $csv = implode("\n", array_map(fn (array $b) => implode(',', $b), $baris));

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-produk-sikasir.csv"',
        ]);
    }

    public function import(Request $request, ProdukImportService $service): RedirectResponse
    {
        // mimes:txt ikut diizinkan karena file .csv acap terdeteksi MIME text/plain
        // oleh browser/OS, bukan text/csv -- validasi Laravel jadi salah tolak kalau tidak.
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'],
        ]);

        $hasil = $service->importDariFile($request->file('file'));

        $pesan = $hasil['berhasil'] > 0
            ? "{$hasil['berhasil']} produk berhasil diimpor."
            : 'Tidak ada produk yang berhasil diimpor.';

        Inertia::flash('import_hasil', $hasil);

        return redirect()->route('admin.onboarding')->with('success', $pesan);
    }
}
