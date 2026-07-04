<?php

namespace App\Support;

use App\Models\Toko;
use Illuminate\Support\Facades\Auth;

/**
 * Toko yang sedang "aktif" untuk request/proses saat ini. Dipakai oleh trait
 * BelongsToToko untuk menyaring query & mengisi id_toko otomatis saat create.
 *
 * Resolusi (dalam urutan ini):
 * 1. Nilai yang di-set manual lewat set() — dipakai seeder/console/test.
 * 2. id_toko milik user yang sedang login.
 * 3. Fallback: satu-satunya toko yang ada di database (berlaku selama masih
 *    fase satu-instance-satu-klien; begitu toko kedua onboard di infra
 *    bersama, rute tamu/publik WAJIB resolusi via subdomain/slug, bukan
 *    fallback ini — lihat catatan Fase 2 di rencana multi-tenant).
 */
class TenantContext
{
    private ?int $tokoId = null;

    private bool $resolved = false;

    public function id(): ?int
    {
        if (! $this->resolved) {
            $this->tokoId = Auth::user()?->id_toko ?? Toko::query()->value('id_toko');
            $this->resolved = true;
        }

        return $this->tokoId;
    }

    public function set(?int $tokoId): void
    {
        $this->tokoId = $tokoId;
        $this->resolved = true;
    }
}
