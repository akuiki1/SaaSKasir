// Branding toko aktif — dibagikan ke semua halaman Inertia lewat
// HandleInertiaRequests::share() (backend), diakses via page.props.toko.
export type Toko = {
    nama: string;
    whatsapp: string | null;
    alamat: string | null;
    instagram: string | null;
    jam_buka: string | null;
    deskripsi: string | null;
    logo_url: string | null;
    lokasi_lat: number | null;
    lokasi_lng: number | null;
};
