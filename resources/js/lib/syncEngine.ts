import {
    markConflict,
    markSynced,
    pendingSales
    
} from '@/lib/offlineDb';
import type {QueuedSale} from '@/lib/offlineDb';

/**
 * Mesin sinkronisasi antrean offline → POST /kasir/transaksi/sync.
 *
 * Otoritas tetap di server: setiap penjualan dijalankan ulang lewat KasirService
 * (validasi stok/promo/pembayaran). Klien hanya menunda pengiriman. `client_uid`
 * membuat retry idempoten (server balas 200 untuk yang sudah tercatat).
 *
 * Auth memakai SESI web same-origin yang sama dengan halaman kasir (rute web,
 * bukan /api) + CSRF via header X-XSRF-TOKEN. Andal di semua host tanpa
 * bergantung SANCTUM_STATEFUL_DOMAINS; tidak ada token disimpan di device.
 */

const SYNC_URL = '/kasir/transaksi/sync';

export interface SyncResult {
    synced: number;
    conflict: number;
    failed: number;
    /** true bila berhenti karena sesi habis (butuh login ulang). */
    authExpired: boolean;
}

let inFlight: Promise<SyncResult> | null = null;

/** Mutex: hanya satu flush berjalan pada satu waktu. */
export function syncPending(): Promise<SyncResult> {
    if (inFlight) {
        return inFlight;
    }

    inFlight = doSync().finally(() => {
        inFlight = null;
    });

    return inFlight;
}

async function doSync(): Promise<SyncResult> {
    const result: SyncResult = {
        synced: 0,
        conflict: 0,
        failed: 0,
        authExpired: false,
    };

    if (typeof navigator !== 'undefined' && !navigator.onLine) {
        return result;
    }

    const queue = await pendingSales();

    for (const sale of queue) {
        let res: Response;

        try {
            res = await postSale(sale);
        } catch {
            // Network error → kemungkinan offline lagi. Biarkan pending, hentikan.
            result.failed++;
            break;
        }

        if (res.ok) {
            // 201 (baru) atau 200 (idempoten, sudah tercatat) → sama-sama sukses.
            const body = await res.json().catch(() => null);
            await markSynced(sale.client_uid, body?.id_transaksi ?? null);
            result.synced++;
            continue;
        }

        if (res.status === 401 || res.status === 419) {
            // Sesi/CSRF habis. Hentikan; UI akan minta kasir login ulang.
            result.authExpired = true;
            result.failed++;
            break;
        }

        if (res.status === 422 || res.status === 409) {
            // Ditolak validasi (mis. stok berubah sejak offline). Tandai konflik.
            const body = await res.json().catch(() => null);
            const message =
                body?.message ||
                'Transaksi ditolak server (kemungkinan stok berubah).';
            await markConflict(sale.client_uid, message);
            result.conflict++;
            continue;
        }

        // 5xx / lainnya → biarkan pending untuk dicoba lagi nanti.
        result.failed++;
    }

    return result;
}

function postSale(sale: QueuedSale): Promise<Response> {
    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    const token = xsrfToken();

    if (token) {
        headers['X-XSRF-TOKEN'] = token;
    }

    return fetch(SYNC_URL, {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: JSON.stringify(sale.payload),
    });
}

/** Baca cookie XSRF-TOKEN Laravel untuk header CSRF Sanctum stateful. */
function xsrfToken(): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}
