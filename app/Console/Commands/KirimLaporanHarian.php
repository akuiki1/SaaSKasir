<?php

namespace App\Console\Commands;

use App\Contracts\WhatsappSender;
use App\Models\Toko;
use App\Services\LaporanHarianService;
use App\Services\PesananService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Kirim laporan tutup toko harian via WhatsApp ke pemilik tiap toko aktif
 * (fitur retensi). Dijadwalkan tiap malam di routes/console.php.
 *
 * Dijalankan TANPA user login → tidak boleh mengandalkan TenantContext dari
 * Auth. Command ini looping SEMUA toko aktif dan LaporanHarianService mengunci
 * TenantContext ke tiap toko sebelum query — kalau tidak, global scope
 * BelongsToToko akan fallback ke "satu-satunya toko di DB" dan hanya satu toko
 * yang terlaporkan (jebakan job terjadwal multi-tenant).
 *
 * Contoh:
 *   php artisan laporan:harian-wa
 *   php artisan laporan:harian-wa --toko=cemilan-mba-tutut --tanggal=2026-07-06
 */
class KirimLaporanHarian extends Command
{
    protected $signature = 'laporan:harian-wa
        {--toko= : slug atau id_toko tertentu (default: semua toko aktif)}
        {--tanggal= : Tanggal laporan YYYY-MM-DD (default: hari ini)}';

    protected $description = 'Kirim laporan tutup toko harian via WhatsApp ke pemilik tiap toko';

    public function handle(LaporanHarianService $laporan, WhatsappSender $wa): int
    {
        $tanggal = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))
            : Carbon::today();

        $tokos = $this->resolveToko();

        if ($tokos->isEmpty()) {
            $this->warn('Tidak ada toko aktif untuk dilaporkan.');

            return self::SUCCESS;
        }

        $terkirim = 0;
        $tanpaNomor = 0;
        $kosong = 0;
        $gagal = 0;

        foreach ($tokos as $toko) {
            if (empty($toko->whatsapp)) {
                $tanpaNomor++;
                $this->line("  • {$toko->nama}: dilewati (belum ada nomor WhatsApp).");

                continue;
            }

            try {
                $pesan = $laporan->bangunPesan($toko, $tanggal);
            } catch (\Throwable $e) {
                $gagal++;
                $this->error("  • {$toko->nama}: gagal menyusun laporan — {$e->getMessage()}");

                continue;
            }

            if ($pesan === null) {
                $kosong++;
                $this->line("  • {$toko->nama}: dilewati (tidak ada transaksi {$tanggal->toDateString()}).");

                continue;
            }

            $tujuan = PesananService::normalizeTelp($toko->whatsapp);

            if ($wa->kirim($tujuan, $pesan)) {
                $terkirim++;
                $this->info("  • {$toko->nama}: terkirim ke {$tujuan}.");
            } else {
                $gagal++;
                $this->error("  • {$toko->nama}: gagal mengirim ke {$tujuan}.");
            }
        }

        $this->newLine();
        $this->info("Selesai. Terkirim: {$terkirim}, tanpa nomor: {$tanpaNomor}, tanpa transaksi: {$kosong}, gagal: {$gagal}.");

        return self::SUCCESS;
    }

    /**
     * Toko target: satu toko (bila --toko) atau semua toko aktif.
     *
     * @return Collection<int, Toko>
     */
    private function resolveToko()
    {
        $tokoArg = $this->option('toko');

        if ($tokoArg) {
            return Toko::where('slug', $tokoArg)
                ->orWhere('id_toko', $tokoArg)
                ->get();
        }

        return Toko::where('status', 'aktif')->get();
    }
}
