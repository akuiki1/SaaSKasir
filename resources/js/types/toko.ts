// Branding toko aktif — dibagikan ke semua halaman Inertia lewat
// HandleInertiaRequests::share() (backend), diakses via page.props.toko.
export type Toko = {
    nama: string;
    slug: string | null;
    /** URL storefront publik (/toko/{slug}) — tombol "Lihat Toko" admin. */
    storefront_url: string | null;
    whatsapp: string | null;
    alamat: string | null;
    instagram: string | null;
    jam_buka: string | null;
    deskripsi: string | null;
    logo_url: string | null;
    lokasi_lat: number | null;
    lokasi_lng: number | null;
};
