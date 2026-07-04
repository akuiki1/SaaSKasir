<?php

namespace App\Support;

/**
 * Helper feature-gating berbasis tier. Semua keputusan "boleh akses fitur X?"
 * lewat sini agar aturannya terpusat (config/langganan.php) dan konsisten
 * antara backend (middleware) & frontend (share Inertia).
 */
class Langganan
{
    /** Urutan/peringkat tier (makin tinggi makin lengkap). Tier tak dikenal = 0. */
    public static function urutanTier(string $tier): int
    {
        return (int) (config("langganan.tiers.{$tier}.urutan") ?? 0);
    }

    /**
     * Apakah tier efektif ini boleh mengakses fitur tsb? Fitur yang tidak
     * terdaftar di config dianggap terbuka untuk semua.
     */
    public static function bolehAkses(string $fitur, string $tierEfektif): bool
    {
        $tierMinimum = config("langganan.fitur.{$fitur}");

        if ($tierMinimum === null) {
            return true;
        }

        return self::urutanTier($tierEfektif) >= self::urutanTier($tierMinimum);
    }

    /**
     * Peta seluruh fitur ber-gate → boolean akses untuk tier ini. Dibagikan ke
     * frontend (page.props.langganan.fitur) untuk mengunci menu/UI.
     *
     * @return array<string, bool>
     */
    public static function fiturUntukTier(string $tierEfektif): array
    {
        $hasil = [];

        foreach (array_keys(config('langganan.fitur', [])) as $fitur) {
            $hasil[$fitur] = self::bolehAkses($fitur, $tierEfektif);
        }

        return $hasil;
    }
}
