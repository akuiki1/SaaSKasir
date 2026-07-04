import { openDB  } from 'idb';
import type {IDBPDatabase} from 'idb';
import type { StrukData } from '@/lib/struk';

/**
 * Lapisan penyimpanan offline PWA kasir (IndexedDB via idb).
 *
 * Menyimpan `queue`: antrean penjualan yang dibuat offline, menunggu disinkron
 * ke POST /api/transaksi. Di-key oleh `client_uid` (UUID) agar retry bersifat
 * idempoten (selaras kolom transaksis.client_uid).
 *
 * Rendering katalog saat offline TIDAK ditangani di sini — service worker
 * meng-cache navigasi /kasir/* (NetworkFirst), dan HTML ter-cache itu sudah
 * membawa props Inertia (produk/promo/pelanggan/layanan) dari kunjungan online
 * terakhir. Jadi halaman kasir tetap terender offline tanpa perlu hidrasi manual.
 *
 * Semua akses dijaga terhadap lingkungan tanpa IndexedDB (SSR) — mengembalikan
 * nilai kosong alih-alih melempar, jadi aman diimpor di mana pun.
 */

/** Payload yang dikirim ke POST /api/transaksi (sama persis dgn jalur web). */
export interface SalePayload {
    metode_pembayaran: string;
    bayar: number;
    id_pelanggan: number | null;
    client_uid: string;
    items: Array<{
        id_produk: number;
        jumlah: number;
        nominal?: number;
        fee?: number;
    }>;
}

export type QueueStatus = 'pending' | 'conflict';

export interface QueuedSale {
    client_uid: string; // keyPath
    payload: SalePayload;
    struk: StrukData; // untuk cetak ulang saat offline
    status: QueueStatus;
    error: string | null; // pesan server saat status 'conflict'
    createdAt: number;
    id_transaksi: number | null; // diisi setelah tersinkron
}

const DB_NAME = 'sikasir-offline';
const DB_VERSION = 1;
const STORE_QUEUE = 'queue';

let dbPromise: Promise<IDBPDatabase> | null = null;

function getDb(): Promise<IDBPDatabase> | null {
    if (typeof indexedDB === 'undefined') {
        return null;
    }

    if (!dbPromise) {
        dbPromise = openDB(DB_NAME, DB_VERSION, {
            upgrade(database) {
                if (!database.objectStoreNames.contains(STORE_QUEUE)) {
                    const store = database.createObjectStore(STORE_QUEUE, {
                        keyPath: 'client_uid',
                    });
                    store.createIndex('status', 'status');
                    store.createIndex('createdAt', 'createdAt');
                }
            },
        });
    }

    return dbPromise;
}

// ---- Antrean penjualan ------------------------------------------------------

export async function enqueueSale(
    payload: SalePayload,
    struk: StrukData,
): Promise<void> {
    const db = getDb();

    if (!db) {
        return;
    }

    const record: QueuedSale = {
        client_uid: payload.client_uid,
        payload,
        struk,
        status: 'pending',
        error: null,
        createdAt: Date.now(),
        id_transaksi: null,
    };

    await (await db).put(STORE_QUEUE, record);
}

/** Semua penjualan berstatus 'pending', urut FIFO (createdAt naik). */
export async function pendingSales(): Promise<QueuedSale[]> {
    const db = getDb();

    if (!db) {
        return [];
    }

    const all = (await (await db).getAllFromIndex(
        STORE_QUEUE,
        'createdAt',
    )) as QueuedSale[];

    return all.filter((sale) => sale.status === 'pending');
}

/** Penjualan yang gagal validasi server (status 'conflict') untuk ditinjau kasir. */
export async function conflictSales(): Promise<QueuedSale[]> {
    const db = getDb();

    if (!db) {
        return [];
    }

    const all = (await (await db).getAllFromIndex(
        STORE_QUEUE,
        'createdAt',
    )) as QueuedSale[];

    return all.filter((sale) => sale.status === 'conflict');
}

export async function countByStatus(status: QueueStatus): Promise<number> {
    const db = getDb();

    if (!db) {
        return 0;
    }

    return (await db).countFromIndex(STORE_QUEUE, 'status', status);
}

/** Tersinkron sukses → buang dari antrean (server sudah jadi otoritas). */
export async function markSynced(
    clientUid: string,
    idTransaksi: number | null,
): Promise<void> {
    const db = getDb();

    if (!db) {
        return;
    }

    // idTransaksi sengaja tak disimpan lama: record dihapus. Argumen dipertahankan
    // untuk kejelasan pemanggil & kemungkinan riwayat singkat di masa depan.
    void idTransaksi;
    await (await db).delete(STORE_QUEUE, clientUid);
}

/** Ditolak server (mis. stok berubah) → tandai konflik, JANGAN buang. */
export async function markConflict(
    clientUid: string,
    error: string,
): Promise<void> {
    const db = getDb();

    if (!db) {
        return;
    }

    const record = (await (await db).get(STORE_QUEUE, clientUid)) as
        | QueuedSale
        | undefined;

    if (!record) {
        return;
    }

    record.status = 'conflict';
    record.error = error;
    await (await db).put(STORE_QUEUE, record);
}

/** Hapus penjualan konflik yang sudah ditinjau/di-void kasir. */
export async function discardSale(clientUid: string): Promise<void> {
    const db = getDb();

    if (!db) {
        return;
    }

    await (await db).delete(STORE_QUEUE, clientUid);
}
