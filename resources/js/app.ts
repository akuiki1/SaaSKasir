import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

// Daftarkan service worker PWA (produksi saja — di dev, vite-plugin-pwa nonaktif).
// registerType 'prompt': versi SW baru MENUNGGU sampai semua tab app ditutup,
// jadi tidak ada reload mendadak di tengah transaksi. Dibungkus try/catch dinamis
// agar kegagalan (mis. header Service-Worker-Allowed belum ada) tidak mematikan app —
// antrean offline (IndexedDB) tetap berfungsi tanpa service worker.
if (import.meta.env.PROD) {
    import('virtual:pwa-register')
        .then(({ registerSW }) => registerSW({ immediate: true }))
        .catch(() => {
            /* SW opsional: abaikan bila registrasi gagal */
        });
}
