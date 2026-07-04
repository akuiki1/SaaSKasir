<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Toko extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_toko';

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

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_toko', 'id_toko');
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
