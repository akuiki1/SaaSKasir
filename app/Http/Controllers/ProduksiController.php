<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Models\Produk;
use App\Models\Produksi;
use App\Models\ProduksiBiaya;
use App\Services\ProduksiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProduksiController extends Controller
{
    use ResolvesPerPage;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $produksis = Produksi::with(['produk', 'biayas'])
            ->when($search !== '', fn ($q) => $q->whereHas('produk', fn ($p) => $p->where('nama', 'like', '%'.$search.'%')))
            ->orderByDesc('id_produksi')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString()
            ->through(fn (Produksi $produksi) => [
                'id_produksi' => $produksi->id_produksi,
                'id_produk' => $produksi->id_produk,
                'produk_nama' => $produksi->produk?->nama ?? 'Produk Terhapus',
                'jumlah' => $produksi->jumlah,
                'total_biaya' => $produksi->total_biaya,
                'modal_per_unit' => $produksi->modal_per_unit,
                'catatan' => $produksi->catatan,
                'tanggal' => Carbon::parse($produksi->created_at)->translatedFormat('d M Y'),
                'biayas' => $produksi->biayas->map(fn (ProduksiBiaya $biaya) => [
                    'nama' => $biaya->nama,
                    'nominal' => $biaya->nominal,
                ])->values(),
            ]);

        // Hanya produk buatan sendiri yang punya batch produksi.
        $produks = Produk::where('jenis', 'produksi')
            ->orderBy('nama')
            ->get(['id_produk', 'nama', 'stok', 'harga_modal']);

        return Inertia::render('admin/Produksi', [
            'produksis' => $produksis,
            'produks' => $produks,
            'stats' => [
                // Agregat lintas halaman — bukan dari data halaman aktif.
                'total_batch' => Produksi::count(),
                'total_unit' => (int) Produksi::sum('jumlah'),
                'total_biaya' => (int) Produksi::sum('total_biaya'),
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $this->resolvePerPage($request),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProduksiService $service): RedirectResponse
    {
        $validated = $request->validate([
            'id_produk' => [
                'required',
                Rule::exists('produks', 'id_produk')->where('jenis', 'produksi'),
            ],
            'jumlah' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            // Mode sederhana: cukup 1 angka total. Mode rinci: daftar biaya bahan.
            'mode' => ['nullable', 'in:sederhana,rinci'],
            'total_biaya' => ['nullable', 'integer', 'min:0'],
            'biayas' => ['nullable', 'array'],
            'biayas.*.nama' => ['required', 'string', 'max:255'],
            'biayas.*.nominal' => ['required', 'integer', 'min:0'],
        ], [
            'id_produk.exists' => 'Produk harus berupa produk buatan sendiri (jenis produksi).',
        ]);

        $service->catat($validated);

        return redirect()->route('admin.produksi')->with('success', 'Batch produksi berhasil dicatat.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produksi $produksi, ProduksiService $service): RedirectResponse
    {
        $service->hapus($produksi);

        return redirect()->route('admin.produksi')->with('success', 'Batch produksi berhasil dihapus.');
    }
}
