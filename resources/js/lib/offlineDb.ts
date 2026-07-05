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
    // Pemilik antrean (id user yang membuat penjualan saat offline). Mencegah
    // sync lintas-akun saat 1 device dipakai bergantian: hanya penjualan milik
    // user yang sedang login yang di-flush. Null = record lama sebelum kolom ini.
    id_user: number | null;
    // Penghitung percobaan sync yang gagal karena error transien (5xx/jaringan
    // tak terduga). Dipakai untuk cap retry agar tidak jadi poison queue.
    attempts: number;
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
    ownerId: number | null = null,
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
        id_user: ownerId,
        attempts: 0,
    };

    await (await db).put(STORE_QUEUE, record);
}

/**
 * Filter kepemilikan: bila ownerId diketahui, hanya record milik user itu
 * (plus record lama tanpa id_user, untuk kompatibilitas upgrade). Bila ownerId
 * null (tak bisa ditentukan), jangan saring — degradasi aman.
 */
function ownedBy(sale: QueuedSale, ownerId: number | null): boolean {
    if (ownerId === null) {
        return true;
    }

    return sale.id_user === ownerId || sale.id_user == null;
}

/** Penjualan 'pending' milik user aktif, urut FIFO (createdAt naik). */
export async function pendingSales(
    ownerId: number | null = null,
): Promise<QueuedSale[]> {
    const db = getDb();

    if (!db) {
        return [];
    }

    const all = (await (await db).getAllFromIndex(
        STORE_QUEUE,
        'createdAt',
    )) as QueuedSale[];

    return all.filter(
        (sale) => sale.status === 'pending' && ownedBy(sale, ownerId),
    );
}

/** Penjualan konflik milik user aktif (gagal validasi server) untuk ditinjau. */
export async function conflictSales(
    ownerId: number | null = null,
): Promise<QueuedSale[]> {
    const db = getDb();

    if (!db) {
        return [];
    }

    const all = (await (await db).getAllFromIndex(
        STORE_QUEUE,
        'createdAt',
    )) as QueuedSale[];

    return all.filter(
        (sale) => sale.status === 'conflict' && ownedBy(sale, ownerId),
    );
}

export async function countByStatus(
    status: QueueStatus,
    ownerId: number | null = null,
): Promise<number> {
    const db = getDb();

    if (!db) {
        return 0;
    }

    // Perlu filter kepemilikan → muat lalu hitung di JS (antrean satu shift kecil),
    // bukan countFromIndex yang tak bisa memfilter id_user.
    const all = (await (await db).getAllFromIndex(
        STORE_QUEUE,
        'status',
        status,
    )) as QueuedSale[];

    return all.filter((sale) => ownedBy(sale, ownerId)).length;
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

/**
 * Naikkan penghitung percobaan gagal (error transien 5xx/jaringan) & kembalikan
 * nilai terbaru. syncEngine memakainya untuk cap retry supaya penjualan yang
 * gagal permanen tak diulang tanpa henti (poison queue).
 */
export async function bumpAttempts(clientUid: string): Promise<number> {
    const db = getDb();

    if (!db) {
        return 0;
    }

    const record = (await (await db).get(STORE_QUEUE, clientUid)) as
        | QueuedSale
        | undefined;

    if (!record) {
        return 0;
    }

    record.attempts = (record.attempts ?? 0) + 1;
    await (await db).put(STORE_QUEUE, record);

    return record.attempts;
}

/** Hapus penjualan konflik yang sudah ditinjau/di-void kasir. */
export async function discardSale(clientUid: string): Promise<void> {
    const db = getDb();

    if (!db) {
        return;
    }

    await (await db).delete(STORE_QUEUE, clientUid);
}
