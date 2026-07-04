// Status langganan toko aktif — dibagikan ke semua halaman Inertia lewat
// HandleInertiaRequests::share() (backend), diakses via page.props.langganan.
export type Langganan = {
    /** Tier efektif setelah memperhitungkan masa berlaku (gratis bila kedaluwarsa). */
    tier: 'gratis' | 'warung' | 'bisnis';
    /** Tier mentah yang tersimpan (sebelum cek kedaluwarsa). */
    tier_langganan: 'gratis' | 'warung' | 'bisnis';
    langganan_sampai: string | null;
    /** Peta fitur ber-gate → boolean akses untuk tier efektif ini. */
    fitur: {
        laporan_keuangan?: boolean;
        [key: string]: boolean | undefined;
    };
};
