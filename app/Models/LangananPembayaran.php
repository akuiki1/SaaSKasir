<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris = satu pembayaran/perpanjangan langganan sebuah toko (ledger
 * append-only). SENGAJA TIDAK pakai trait BelongsToToko (mirror alasan
 * User/Toko): dikelola lintas-tenant oleh platform lewat command/webhook yang
 * jalan tanpa konteks tenant (CLI). Akses sisi toko selalu eksplisit lewat
 * relasi Toko::pembayarans(), jadi tetap ter-scope tanpa global scope.
 */
class LangananPembayaran extends Model
{
    protected $table = 'langganan_pembayarans';

    protected $primaryKey = 'id_langganan_pembayaran';

    protected $fillable = [
        'id_toko',
        'tier',
        'nominal',
        'metode',
        'periode_mulai',
        'periode_sampai',
        'id_user',
        'catatan',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'periode_mulai' => 'date',
        'periode_sampai' => 'date',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'id_toko', 'id_toko');
    }
}
