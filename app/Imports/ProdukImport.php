<?php

namespace App\Imports;

use App\Models\Kategori;
use App\Services\ProdukService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import massal produk (tipe_jual=satuan saja -- curah/jasa tetap ditambah
 * manual lewat panel produk) dari template CSV/Excel saat onboarding.
 * Pembuatan tiap baris didelegasikan ke ProdukService::buat() agar aturan
 * bisnis (default satuan, generate barcode, catat mutasi stok awal) tetap
 * konsisten dengan tambah produk manual, tanpa duplikasi logika.
 */
class ProdukImport implements ToCollection, WithHeadingRow
{
    public int $berhasil = 0;

    /** @var array<int, string> */
    public array $dilewati = [];

    public function __construct(private readonly ProdukService $produkService) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            // +1 karena heading row, +1 lagi karena index basis-0 -> nomor baris asli di file.
            $baris = $i + 2;

            $validator = Validator::make($row->toArray(), [
                'nama' => ['required', 'string', 'max:255'],
                'kategori' => ['required', 'string', 'max:255'],
                'harga_jual' => ['required', 'numeric', 'min:0'],
                'stok' => ['required', 'numeric', 'min:0'],
                'barcode' => ['nullable', 'string', 'max:255', 'unique:produks,barcode'],
            ]);

            if ($validator->fails()) {
                $this->dilewati[] = "Baris {$baris}: ".$validator->errors()->first();

                continue;
            }

            $data = $validator->validated();
            $kategori = $this->resolveKategori($data['kategori']);

            $this->produkService->buat([
                'id_kategori' => $kategori->id_kategori,
                'nama' => trim($data['nama']),
                'tipe_jual' => 'satuan',
                'harga_jual' => (int) round((float) $data['harga_jual']),
                'stok' => (float) $data['stok'],
                'barcode' => filled($data['barcode'] ?? null) ? $data['barcode'] : null,
            ]);

            $this->berhasil++;
        }
    }

    /**
     * Cocokkan kategori tanpa peduli besar/kecil huruf (mis. "Minuman" dan
     * "minuman" dianggap sama) supaya import tidak bikin kategori duplikat
     * hanya karena beda kapitalisasi antar baris.
     */
    private function resolveKategori(string $nama): Kategori
    {
        $nama = trim($nama);

        return Kategori::whereRaw('LOWER(nama_kategori) = ?', [mb_strtolower($nama)])->first()
            ?? Kategori::create(['nama_kategori' => $nama]);
    }
}
