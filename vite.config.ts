import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
        VitePWA({
            // Registrasi SW dilakukan manual di resources/js/app.ts agar kegagalan
            // (mis. header Service-Worker-Allowed belum ada di server non-Apache)
            // tidak fatal — antrean offline tetap jalan tanpa SW.
            injectRegister: false,
            // 'prompt': SW baru MENUNGGU sampai app benar-benar ditutup, tidak
            // reload mendadak di tengah shift kasir. Antrean aman di IndexedDB.
            registerType: 'prompt',
            strategies: 'generateSW',
            // Vite mem-build ke public/build tapi base root Laravel adalah public/.
            buildBase: '/build/',
            // Scope root wajib agar SW bisa mengintersep navigasi /kasir/*.
            // Butuh header `Service-Worker-Allowed: /` di /build/sw.js (public/.htaccess).
            scope: '/',
            includeAssets: ['favicon.svg', 'favicon.ico', 'icons/*.png'],
            manifest: {
                name: 'SiKasir — Kasir Toko',
                short_name: 'SiKasir',
                description: 'POS kasir SiKasir — tetap jualan walau internet mati.',
                lang: 'id',
                theme_color: '#059669',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/kasir/dashboard',
                scope: '/',
                icons: [
                    { src: '/icons/pwa-192x192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/pwa-512x512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/maskable-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,woff2,png,svg,ico}'],
                // Laravel menyajikan HTML dari server; jangan pakai SPA fallback.
                navigateFallback: undefined,
                maximumFileSizeToCacheInBytes: 4 * 1024 * 1024,
                cleanupOutdatedCaches: true,
                runtimeCaching: [
                    {
                        // Navigasi halaman kasir → NetworkFirst supaya shell tersedia offline.
                        urlPattern: ({ url, request }) =>
                            request.mode === 'navigate' && url.pathname.startsWith('/kasir'),
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'kasir-pages',
                            networkTimeoutSeconds: 3,
                            expiration: { maxEntries: 20, maxAgeSeconds: 7 * 24 * 3600 },
                        },
                    },
                    {
                        // Foto produk & gambar (dipakai grid kasir offline).
                        urlPattern: ({ url }) =>
                            url.pathname.startsWith('/storage') || url.pathname.startsWith('/images'),
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'kasir-assets',
                            expiration: { maxEntries: 200, maxAgeSeconds: 30 * 24 * 3600 },
                        },
                    },
                ],
            },
            devOptions: { enabled: false },
        }),
    ],
});
