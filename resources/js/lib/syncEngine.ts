import {
    bumpAttempts,
    markConflict,
    markSynced,
    pendingSales,
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

/** Batas percobaan sync untuk error transien sebelum ditandai konflik (poison queue). */
const MAX_SYNC_ATTEMPTS = 5;

export interface SyncResult {
    synced: number;
    conflict: number;
    failed: number;
    /** true bila berhenti karena sesi habis (butuh login ulang). */
    authExpired: boolean;
}

let inFlight: Promise<SyncResult> | null = null;

/** Mutex: hanya satu flush berjalan pada satu waktu. */
export function syncPending(ownerId: number | null = null): Promise<SyncResult> {
    if (inFlight) {
        return inFlight;
    }

    inFlight = doSync(ownerId).finally(() => {
        inFlight = null;
    });

    return inFlight;
}

async function doSync(ownerId: number | null): Promise<SyncResult> {
    const result: SyncResult = {
        synced: 0,
        conflict: 0,
        failed: 0,
        authExpired: false,
    };

    if (typeof navigator !== 'undefined' && !navigator.onLine) {
        return result;
    }

    const queue = await pendingSales(ownerId);

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
            // 201 (baru) atau 200 (idempoten, sudah tercatat) → sukses HANYA bila
            // body benar dari server kita (client_uid cocok). Captive portal /
            // proxy transparan bisa membalas 200 berisi HTML login; menghapus
            // antrean atas dasar itu = transaksi HILANG. Jangan ambil risiko:
            // hentikan & biarkan pending sampai koneksi benar-benar tembus.
            const body = await res.json().catch(() => null);

            if (body?.client_uid === sale.client_uid) {
                await markSynced(sale.client_uid, body.id_transaksi ?? null);
                result.synced++;
                continue;
            }

            result.failed++;
            break;
        }

        if (res.status === 401 || res.status === 419) {
            // Sesi/CSRF habis. Hentikan; UI akan minta kasir login ulang.
            result.authExpired = true;
            result.failed++;
            break;
        }

        if (res.status >= 400 && res.status < 500) {
            // 4xx non-auth = ditolak permanen (stok berubah, produk terhapus,
            // lintas-toko 404, validasi). Retry tak menolong → tandai konflik.
            const body = await res.json().catch(() => null);
            const message =
                body?.message ||
                'Transaksi ditolak server (kemungkinan stok berubah).';
            await markConflict(sale.client_uid, message);
            result.conflict++;
            continue;
        }

        // 5xx / error transien → biarkan pending, tapi cap retry agar tidak jadi
        // poison queue (server rusak permanen). Lewat batas → tandai konflik.
        const attempts = await bumpAttempts(sale.client_uid);

        if (attempts >= MAX_SYNC_ATTEMPTS) {
            await markConflict(
                sale.client_uid,
                'Gagal disinkron berkali-kali (server bermasalah). Tinjau manual.',
            );
            result.conflict++;
            continue;
        }

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
