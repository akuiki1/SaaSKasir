<?php

namespace App\Models;

use App\Models\Concerns\BelongsToToko;
use Database\Factories\TransaksiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    /** @use HasFactory<TransaksiFactory> */
    use BelongsToToko, HasFactory;

    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_user',
        'id_pelanggan',
        'id_promo',
        'total_harga',
        'diskon',
        'metode_pembayaran',
        'bayar',
        'kembalian',
        // Idempotensi sinkronisasi offline (bukan data tenant-sensitif). Diisi
        // hanya oleh transaksi hasil antrean PWA; penjualan web biasa null.
        'client_uid',
    ];

    protected $casts = [
        'total_harga' => 'integer',
        'diskon' => 'integer',
        'bayar' => 'integer',
        'kembalian' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id')->withDefault([
            'name' => 'User Terhapus',
        ]);
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'id_promo', 'id_promo');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan')->withDefault([
            'nama' => 'Umum',
        ]);
    }

    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
