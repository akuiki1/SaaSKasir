import { usePage } from '@inertiajs/vue3';
import { onMounted, readonly, ref } from 'vue';
import { countByStatus } from '@/lib/offlineDb';
import { syncPending  } from '@/lib/syncEngine';
import type {SyncResult} from '@/lib/syncEngine';

/**
 * State offline/sinkronisasi global (singleton level-modul) yang dibagi antara
 * banner status, badge antrean, dan halaman kasir. Listener online/offline
 * dipasang sekali seumur app (Inertia SPA), tidak dilepas saat pindah halaman.
 */

const online = ref(typeof navigator !== 'undefined' ? navigator.onLine : true);
const pendingCount = ref(0);
const conflictCount = ref(0);
const syncing = ref(false);
const authExpired = ref(false);

let listenersBound = false;
let lastResult: SyncResult | null = null;

// Id user yang sedang login — antrean di-scope ke pemiliknya agar penjualan
// luring milik kasir A tidak ikut ter-sync (dan salah atribusi) saat kasir B
// login di device yang sama. Diisi saat useOffline() dipanggil di setup komponen.
let currentOwnerId: number | null = null;

async function refreshCounts(): Promise<void> {
    pendingCount.value = await countByStatus('pending', currentOwnerId);
    conflictCount.value = await countByStatus('conflict', currentOwnerId);
}

/** Jalankan flush antrean (aman dipanggil berkali-kali; mutex di syncEngine). */
async function runSync(): Promise<SyncResult | null> {
    if (!online.value || syncing.value) {
        return null;
    }

    syncing.value = true;

    try {
        lastResult = await syncPending(currentOwnerId);
        authExpired.value = lastResult.authExpired;

        return lastResult;
    } finally {
        syncing.value = false;
        await refreshCounts();
    }
}

function handleOnline(): void {
    online.value = true;
    void runSync();
}

function handleOffline(): void {
    online.value = false;
}

export function useOffline() {
    // Tangkap id user aktif dari shared props Inertia (auth.user) untuk men-scope
    // antrean ke pemiliknya. usePage() aman dipanggil di sini karena useOffline
    // hanya dipakai di setup komponen.
    const page = usePage();
    const authUser = (page.props.auth as { user?: { id?: number } } | undefined)
        ?.user;
    currentOwnerId = typeof authUser?.id === 'number' ? authUser.id : null;

    onMounted(() => {
        if (typeof window === 'undefined') {
            return;
        }

        void refreshCounts();

        if (!listenersBound) {
            listenersBound = true;
            window.addEventListener('online', handleOnline);
            window.addEventListener('offline', handleOffline);

            // Coba sinkron sisa antrean begitu app dibuka (bila online).
            if (online.value) {
                void runSync();
            }
        }
    });

    return {
        online: readonly(online),
        pendingCount: readonly(pendingCount),
        conflictCount: readonly(conflictCount),
        syncing: readonly(syncing),
        authExpired: readonly(authExpired),
        /** Id user aktif — dipakai untuk menandai record antrean saat enqueue. */
        ownerId: () => currentOwnerId,
        /** Flush manual (tombol "Sinkron sekarang"). */
        sync: runSync,
        /** Muat ulang hitungan dari IndexedDB (mis. setelah enqueue baru). */
        refresh: refreshCounts,
    };
}
