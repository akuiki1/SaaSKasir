<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Toko extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_toko';

    // CATATAN: `tier` & `langganan_sampai` SENGAJA TIDAK fillable (sama alasan
    // dengan id_toko) — kalau bisa di-mass-assign, admin toko bisa menaikkan
    // tier-nya sendiri lewat form settings apa pun & melewati paywall. Hanya
    // LanggananService (via forceFill) / command / seed yang boleh mengubahnya.
    protected $fillable = [
        'nama',
        'slug',
        'whatsapp',
        'alamat',
        'instagram',
        'jam_buka',
        'deskripsi',
        'logo',
        'lokasi_lat',
        'lokasi_lng',
        'status',
    ];

    protected $casts = [
        'langganan_sampai' => 'date',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_toko', 'id_toko');
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(LangananPembayaran::class, 'id_toko', 'id_toko');
    }

    /**
     * Tier yang benar-benar aktif setelah mempertimbangkan masa berlaku.
     * Fungsi murni tanpa side-effect — dipakai middleware gating, share Inertia,
     * dan halaman langganan.
     *
     * - tier 'gratis' → selalu gratis.
     * - tier berbayar + langganan_sampai NULL → tier itu (perpetual/comp, mis. grandfather).
     * - tier berbayar + langganan_sampai >= hari ini → tier itu.
     * - tier berbayar + langganan_sampai lampau → gratis (kedaluwarsa).
     */
    public function tierEfektif(): string
    {
        if ($this->tier === 'gratis') {
            return 'gratis';
        }

        if ($this->langganan_sampai === null) {
            return $this->tier;
        }

        return $this->langganan_sampai->gte(Carbon::today()) ? $this->tier : 'gratis';
    }

    /**
     * URL logo toko yang siap ditampilkan (sama pola dengan Produk::fotoUrl):
     * path relatif hasil upload dilayani lewat symlink storage, URL lengkap
     * dikembalikan apa adanya. Null bila toko belum unggah logo sendiri —
     * biarkan pemanggil fallback ke logo default, JANGAN ke logo toko lain.
     */
    public static function logoUrl(?string $logo): ?string
    {
        if (! $logo) {
            return null;
        }

        if (Str::startsWith($logo, ['http://', 'https://'])) {
            return $logo;
        }

        return asset('storage/'.$logo);
    }
}
