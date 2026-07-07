import { usePage } from '@inertiajs/vue3';
import {
    BadgePercent,
    Boxes,
    CircleDollarSign,
    ClipboardList,
    ContactRound,
    CreditCard,
    Factory,
    History,
    LayoutGrid,
    Package,
    ReceiptText,
    ShoppingCart,
    Store,
    Tags,
    TrendingUp,
    UserCog,
    UsersRound,
    Wallet,
    Warehouse,
} from 'lucide-vue-next';
import { computed } from 'vue';
import type { NavBadge, NavGroup, NavItem } from '@/types';

/** Satu hasil pencarian menu: item + label grup asalnya (untuk konteks di palette). */
export type NavSearchEntry = {
    item: NavItem;
    group: string;
};

/** Dashboard "rumah" tiap peran — dipakai sidebar (logo) & menu. */
export const DASHBOARD_PER_ROLE: Record<string, string> = {
    ceo: '/ceo/dashboard',
    superadmin: '/superadmin/dashboard',
    admin: '/admin/dashboard',
    kasir: '/kasir/dashboard',
};

/**
 * Sumber tunggal definisi menu navigasi per peran (ceo, superadmin, admin,
 * kasir). Dipakai oleh sidebar (AppSidebar/NavMain) dan command-palette di
 * header (MenuSearch) agar tidak ganda.
 */
export function useNavMenu() {
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const role = computed(() => (user.value?.role as string) ?? 'kasir');
    const isAdmin = computed(() => role.value === 'admin');
    // Peran platform (kantor pusat SiKasir): lintas-toko, panelnya sendiri.
    const isPlatform = computed(
        () => role.value === 'ceo' || role.value === 'superadmin',
    );

    const homeHref = computed(
        () => DASHBOARD_PER_ROLE[role.value] ?? '/kasir/dashboard',
    );

    // Dashboard disematkan di atas (di luar grup) untuk admin & peran platform.
    const pinned = computed<NavItem | undefined>(() =>
        isAdmin.value || isPlatform.value
            ? { title: 'Dashboard', href: homeHref.value, icon: LayoutGrid }
            : undefined,
    );

    const groups = computed<NavGroup[]>(() => {
        // CEO: murni pemantauan — seluruh isinya ada di satu dashboard,
        // sidebar sengaja hanya berisi Dashboard (pinned) tanpa grup lain.
        if (role.value === 'ceo') {
            return [];
        }

        // Super Admin: operasional platform — kelola toko & admin.
        if (role.value === 'superadmin') {
            return [
                {
                    label: 'Kelola Platform',
                    items: [
                        {
                            title: 'Kelola Toko',
                            href: '/superadmin/toko',
                            icon: Store,
                        },
                        {
                            title: 'Kelola Admin',
                            href: '/superadmin/admins',
                            icon: UserCog,
                        },
                    ],
                },
            ];
        }

        if (isAdmin.value) {
            // Badge dari shared props (lihat HandleInertiaRequests); tampil bila > 0.
            const b = page.props.sidebarBadges;
            const pesananBadge: NavBadge | undefined =
                b && b.pesananAktif > 0
                    ? { count: b.pesananAktif, tone: 'order' }
                    : undefined;
            const stokBadge: NavBadge | undefined =
                b && b.stokMenipis > 0
                    ? { count: b.stokMenipis, tone: 'warn' }
                    : undefined;

            // Laporan Keuangan terkunci untuk tier gratis (paywall). Item yang
            // terkunci diarahkan ke halaman langganan + ditandai gembok.
            const keuanganLocked =
                page.props.langganan?.fitur?.laporan_keuangan === false;

            return [
                {
                    label: 'Penjualan',
                    items: [
                        {
                            title: 'Transaksi POS (Kasir)',
                            href: '/kasir/transaksi',
                            icon: ShoppingCart,
                        },
                        {
                            title: 'Data Transaksi',
                            href: '/admin/transactions',
                            icon: ReceiptText,
                        },
                        {
                            title: 'Pesanan Online',
                            href: '/admin/pesanan',
                            icon: ClipboardList,
                            badge: pesananBadge,
                        },
                        {
                            title: 'Pelanggan',
                            href: '/admin/pelanggan',
                            icon: ContactRound,
                        },
                        {
                            title: 'Promo',
                            href: '/admin/promos',
                            icon: BadgePercent,
                        },
                    ],
                },
                {
                    label: 'Produk & Stok',
                    items: [
                        {
                            title: 'Kategori',
                            href: '/admin/kategori',
                            icon: Tags,
                        },
                        {
                            title: 'Data Produk',
                            href: '/admin/products',
                            icon: Package,
                        },
                        {
                            title: 'Manajemen Stok',
                            href: '/admin/stok',
                            icon: Warehouse,
                            badge: stokBadge,
                        },
                        {
                            title: 'Produksi',
                            href: '/admin/produksi',
                            icon: Factory,
                        },
                    ],
                },
                {
                    // Pencatatan uang keluar — fungsinya "input", bukan "laporan",
                    // jadi dipisah dari grup analisis di bawah.
                    label: 'Keuangan',
                    items: [
                        {
                            title: 'Pengeluaran',
                            href: '/admin/pengeluarans',
                            icon: CircleDollarSign,
                        },
                    ],
                },
                {
                    label: 'Laporan & Analisis',
                    items: [
                        {
                            title: 'Analisis Penjualan',
                            href: '/admin/laporan/penjualan',
                            icon: TrendingUp,
                        },
                        {
                            title: 'Stok & Inventaris',
                            href: '/admin/laporan/inventaris',
                            icon: Boxes,
                        },
                        {
                            title: 'Laporan Keuangan',
                            href: keuanganLocked
                                ? '/admin/langganan'
                                : '/admin/laporan/keuangan',
                            icon: Wallet,
                            locked: keuanganLocked,
                        },
                        {
                            title: 'Analisis Pelanggan',
                            href: '/admin/laporan/pelanggan',
                            icon: UsersRound,
                        },
                    ],
                },
                {
                    label: 'Sistem',
                    items: [
                        {
                            title: 'Data User',
                            href: '/admin/users',
                            icon: UserCog,
                        },
                        {
                            title: 'Langganan',
                            href: '/admin/langganan',
                            icon: CreditCard,
                        },
                    ],
                },
            ];
        }

        // Kasir hanya punya sedikit menu — tampilkan datar tanpa label kategori.
        return [
            {
                label: '',
                items: [
                    {
                        title: 'Dashboard',
                        href: '/kasir/dashboard',
                        icon: LayoutGrid,
                    },
                    {
                        title: 'Transaksi',
                        href: '/kasir/transaksi',
                        icon: ShoppingCart,
                    },
                    {
                        title: 'Pesanan Online',
                        href: '/kasir/pesanan',
                        icon: ClipboardList,
                    },
                    {
                        title: 'Riwayat Transaksi',
                        href: '/kasir/riwayat',
                        icon: History,
                    },
                ],
            },
        ];
    });

    // Daftar datar (pinned + semua item grup) untuk dicari di command-palette.
    const searchEntries = computed<NavSearchEntry[]>(() => {
        const entries: NavSearchEntry[] = [];

        if (pinned.value) {
            entries.push({ item: pinned.value, group: '' });
        }

        groups.value.forEach((g) => {
            g.items.forEach((item) => entries.push({ item, group: g.label }));
        });

        return entries;
    });

    return { role, isAdmin, isPlatform, homeHref, pinned, groups, searchEntries };
}
