<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Kelola akun admin (pemilik toko) lintas-tenant — khusus superadmin.
 * Sengaja SEMPIT: lihat & reset password saja. Membuat admin baru lewat
 * "Tambah Toko" (TokoController::store — admin selalu lahir bersama
 * tokonya), dan staf kasir tetap urusan admin masing-masing toko.
 */
class AdminController extends Controller
{
    use ResolvesPerPage;

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $admins = User::where('role', 'admin')
            ->with('toko:id_toko,nama,slug,status')
            ->when($search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhereHas('toko', fn ($t) => $t->where('nama', 'like', '%'.$search.'%'))))
            ->orderByDesc('created_at')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString()
            ->through(fn (User $admin) => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'toko' => $admin->toko?->only(['nama', 'slug', 'status']),
                'created_at' => $admin->created_at?->toDateString(),
            ]);

        return Inertia::render('superadmin/Admins', [
            'admins' => $admins,
            'stats' => [
                'total_admin' => User::where('role', 'admin')->count(),
                // Toko tanpa satu pun admin = terkunci dari sisi pemilik —
                // sinyal follow-up penting untuk superadmin.
                'toko_tanpa_admin' => Toko::whereDoesntHave('users', fn ($q) => $q->where('role', 'admin'))->count(),
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $this->resolvePerPage($request),
            ],
        ]);
    }

    /**
     * Reset password admin toko (mis. pemilik lupa password & tidak bisa
     * lewat email). Hanya berlaku untuk akun role=admin — akun lain 404,
     * bukan 403, agar tidak membocorkan keberadaan ID di luar cakupan.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'admin') {
            throw new NotFoundHttpException;
        }

        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', "Password {$user->name} berhasil direset.");
    }
}
