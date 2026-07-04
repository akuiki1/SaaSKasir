<?php

namespace App\Models\Concerns;

use App\Models\Toko;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Isolasi data multi-tenant: query otomatis disaring ke toko yang sedang
 * aktif (TenantContext), dan baris baru otomatis diberi id_toko-nya —
 * tanpa perlu mengubah controller yang sudah ada.
 *
 * id_toko sengaja TIDAK dimasukkan ke $fillable model manapun: pengisiannya
 * hanya lewat jalur ini (atau eksplisit di kode server), bukan dari input
 * pengguna, supaya tidak ada celah "loncat toko" lewat mass assignment.
 */
trait BelongsToToko
{
    protected static function bootBelongsToToko(): void
    {
        static::addGlobalScope('toko', function (Builder $builder) {
            $id = app(TenantContext::class)->id();

            if ($id !== null) {
                $builder->where($builder->getModel()->getTable().'.id_toko', $id);
            }
        });

        static::creating(function ($model) {
            if (! $model->id_toko && $id = app(TenantContext::class)->id()) {
                $model->id_toko = $id;
            }
        });
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'id_toko', 'id_toko');
    }
}
