<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\LanggananService;
use App\Services\RegistrasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kelola toko lintas-tenant (khusus superadmin): daftar semua toko dengan
 * kesehatan singkatnya, aktif/nonaktifkan, catat perpanjangan langganan
 * (reuse LanggananService — jalur yang sama dengan command langganan:catat),
 * dan onboarding toko baru (reuse RegistrasiService — jalur yang sama dengan
 * registrasi mandiri, jadi aturan slug/verifikasi email tetap satu sumber).
 */
class TokoController extends Controller
{
    use ResolvesPerPage;

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : '';
        $tier = $request->query('tier', '');
        $tier = array_key_exists($tier, config('langganan.tiers')) ? $tier : '';

        $tokos = Toko::query()
            ->withCount('users')
            ->when($search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('nama', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($tier !== '', fn ($q) => $q->where('tier', $tier))
            ->orderByDesc('created_at')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString();

        // Denyut 30 hari per toko dalam SATU query agregat (bukan N+1) —
        // lintas-tenant, scope global dilepas eksplisit.
        $denyut = Transaksi::withoutGlobalScope('toko')
            ->where('created_at', '>=', Carbon::today()->subDays(30)->startOfDay())
            ->whereIn('id_toko', $tokos->getCollection()->pluck('id_toko'))
            ->selectRaw('id_toko, COUNT(*) as jumlah, COALESCE(SUM(total_harga), 0) as omzet')
            ->groupBy('id_toko')
            ->get()
            ->keyBy('id_toko');

        // Nama admin pemilik per toko halaman ini (User tak ber-global-scope).
        $adminPerToko = User::where('role', 'admin')
            ->whereIn('id_toko', $tokos->getCollection()->pluck('id_toko'))
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'id_toko'])
            ->groupBy('id_toko');

        $tokos->through(fn (Toko $toko) => [
            'id_toko' => $toko->id_toko,
            'nama' => $toko->nama,
            'slug' => $toko->slug,
            'status' => $toko->status,
            'tier' => $toko->tier,
            'tier_efektif' => $toko->tierEfektif(),
            'langganan_sampai' => $toko->langganan_sampai?->toDateString(),
            'users_count' => $toko->users_count,
            'admin' => $adminPerToko->get($toko->id_toko)?->first()?->only(['name', 'email']),
            'trx_30hari' => (int) ($denyut[$toko->id_toko]->jumlah ?? 0),
            'omzet_30hari' => (int) ($denyut[$toko->id_toko]->omzet ?? 0),
            'created_at' => $toko->created_at?->toDateString(),
        ]);

        return Inertia::render('superadmin/Toko', [
            'tokos' => $tokos,
            'stats' => [
                'total' => Toko::count(),
                'aktif' => Toko::where('status', 'aktif')->count(),
                'nonaktif' => Toko::where('status', '!=', 'aktif')->count(),
            ],
            'tiers' => collect(config('langganan.tiers'))
                ->map(fn (array $t, string $key) => ['key' => $key, 'label' => $t['label'], 'harga' => $t['harga']])
                ->values(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'tier' => $tier,
                'per_page' => $this->resolvePerPage($request),
            ],
        ]);
    }

    /**
     * Onboarding toko baru oleh superadmin (jalur high-touch): buat Toko +
     * admin pemiliknya sekaligus lewat RegistrasiService.
     */
    public function store(Request $request, RegistrasiService $registrasi): RedirectResponse
    {
        $validated = $request->validate([
            'nama_toko' => ['required', 'string', 'max:255'],
            'nama_pemilik' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $registrasi->daftarkanToko([
            'nama_toko' => $validated['nama_toko'],
            'nama_pemilik' => $validated['nama_pemilik'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? '',
            'password' => $validated['password'],
        ]);

        return redirect()->route('superadmin.toko')->with('success', 'Toko baru berhasil didaftarkan.');
    }

    public function updateStatus(Request $request, Toko $toko): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        $toko->update(['status' => $validated['status']]);

        $label = $validated['status'] === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Toko {$toko->nama} berhasil {$label}.");
    }

    /**
     * Catat pembayaran/perpanjangan langganan — jalur yang sama persis dengan
     * command langganan:catat (LanggananService), hanya beda pintu masuk.
     */
    public function perpanjang(Request $request, Toko $toko, LanggananService $langganan): RedirectResponse
    {
        $tierBerbayar = array_keys(array_filter(
            config('langganan.tiers'),
            fn (array $t) => $t['harga'] > 0,
        ));

        $validated = $request->validate([
            'tier' => ['required', Rule::in($tierBerbayar)],
            'bulan' => ['required', 'integer', 'min:1', 'max:24'],
            'nominal' => ['nullable', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $langganan->catatPembayaran(
            toko: $toko,
            tier: $validated['tier'],
            bulan: (int) $validated['bulan'],
            nominal: isset($validated['nominal']) ? (int) $validated['nominal'] : null,
            metode: 'manual',
            userId: $request->user()->id,
            catatan: $validated['catatan'] ?? null,
        );

        return back()->with('success', "Langganan {$toko->nama} berhasil diperpanjang.");
    }
}
