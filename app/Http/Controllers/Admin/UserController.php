<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * User TIDAK memakai trait BelongsToToko (lihat catatan di model User), jadi
 * setiap query & mutasi di sini di-scope MANUAL ke toko aktif — termasuk
 * baris yang datang lewat route model binding ({user}), yang tidak otomatis
 * tersaring seperti model ber-trait lainnya.
 */
class UserController extends Controller
{
    use ResolvesPerPage;

    public function __construct(private readonly TenantContext $tenant) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $role = $request->query('role', '');
        $role = in_array($role, ['admin', 'kasir'], true) ? $role : '';

        $users = $this->tokoUsers()
            ->when($search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')))
            ->when($role !== '', fn ($q) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
            'stats' => [
                // Agregat lintas halaman — bukan dari data halaman aktif.
                'total_users' => $this->tokoUsers()->count(),
                'total_admin' => $this->tokoUsers()->where('role', 'admin')->count(),
                'total_kasir' => $this->tokoUsers()->where('role', 'kasir')->count(),
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
                'per_page' => $this->resolvePerPage($request),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // forceCreate: id_toko sengaja tidak fillable (lihat User model), jadi
        // User::create() biasa akan diam-diam membuat staf tanpa toko.
        User::forceCreate([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'id_toko' => $this->tenant->id(),
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->abortUnlessSameToko($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
        ]);

        // Cegah menurunkan peran admin terakhir agar panel admin tidak terkunci.
        if ($user->role === 'admin' && $validated['role'] !== 'admin' && $this->isLastAdmin()) {
            return redirect()->route('admin.users')->with('error', 'Tidak dapat menurunkan peran admin terakhir.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->abortUnlessSameToko($user);

        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Cegah menghapus admin terakhir agar selalu ada yang bisa mengelola sistem.
        if ($user->role === 'admin' && $this->isLastAdmin()) {
            return redirect()->route('admin.users')->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus.');
    }

    /** Staf milik toko yang sedang aktif. */
    private function tokoUsers()
    {
        return User::where('id_toko', $this->tenant->id());
    }

    /** Apakah toko aktif hanya tersisa satu akun admin? */
    private function isLastAdmin(): bool
    {
        return $this->tokoUsers()->where('role', 'admin')->count() <= 1;
    }

    /**
     * 404 (bukan 403 — hindari membocorkan bahwa ID tersebut ada di toko lain)
     * bila user yang di-route-model-binding ternyata milik toko lain. Route
     * model binding {user} tidak otomatis ter-scope seperti model ber-trait.
     */
    private function abortUnlessSameToko(User $user): void
    {
        if ($user->id_toko !== $this->tenant->id()) {
            throw new NotFoundHttpException;
        }
    }
}
