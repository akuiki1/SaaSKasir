<?php

namespace App\Console\Commands;

use App\Models\Toko;
use App\Services\LanggananService;
use Illuminate\Console\Command;

/**
 * Catat pembayaran langganan sebuah toko secara MANUAL (fase high-touch: owner
 * SiKasir menagih & menandai toko sudah bayar). Ini jalur pembayaran resmi
 * untuk sekarang — kelak digantikan/ditambah webhook gateway yang memanggil
 * LanggananService yang sama.
 *
 * Contoh: php artisan langganan:catat cemilan-mba-tutut warung --bulan=1
 */
class CatatLangganan extends Command
{
    protected $signature = 'langganan:catat
        {toko : slug atau id_toko}
        {tier : warung|bisnis}
        {--bulan=1 : Jumlah bulan yang dibayar}
        {--nominal= : Nominal dibayar (default: harga tier x bulan)}
        {--catatan= : Catatan bebas}';

    protected $description = 'Catat pembayaran langganan toko secara manual & perpanjang masa berlaku';

    public function handle(LanggananService $service): int
    {
        $tier = $this->argument('tier');

        if (! in_array($tier, ['warung', 'bisnis'], true)) {
            $this->error("Tier tidak valid: {$tier}. Pilih 'warung' atau 'bisnis'.");

            return self::FAILURE;
        }

        $tokoArg = $this->argument('toko');
        $toko = Toko::where('slug', $tokoArg)->orWhere('id_toko', $tokoArg)->first();

        if (! $toko) {
            $this->error("Toko tidak ditemukan: {$tokoArg}");

            return self::FAILURE;
        }

        $bulan = max(1, (int) $this->option('bulan'));
        $nominal = $this->option('nominal') !== null ? (int) $this->option('nominal') : null;

        $pembayaran = $service->catatPembayaran(
            toko: $toko,
            tier: $tier,
            bulan: $bulan,
            nominal: $nominal,
            metode: 'manual',
            userId: null,
            catatan: $this->option('catatan'),
        );

        $this->info("Langganan {$toko->nama} diperpanjang.");
        $this->line("  Tier          : {$tier}");
        $this->line("  Nominal       : Rp".number_format($pembayaran->nominal, 0, ',', '.'));
        $this->line("  Periode       : {$pembayaran->periode_mulai->toDateString()} s/d {$pembayaran->periode_sampai->toDateString()}");
        $this->line("  Berlaku sampai: {$toko->fresh()->langganan_sampai->toDateString()}");

        return self::SUCCESS;
    }
}
