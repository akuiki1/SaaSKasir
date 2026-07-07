<?php

namespace App\Services\Whatsapp;

use App\Contracts\WhatsappSender;
use Illuminate\Support\Facades\Log;

/**
 * Driver WhatsApp default: tulis pesan ke log alih-alih benar-benar mengirim.
 * Gratis, tanpa kredensial, aman di dev/CI. Selalu "berhasil" (true) — jadi
 * seluruh pipeline laporan harian bisa diuji end-to-end tanpa gateway live.
 */
class LogWhatsappSender implements WhatsappSender
{
    public function kirim(string $tujuan, string $pesan): bool
    {
        Log::info('[WA] laporan harian (driver log — tidak benar-benar terkirim)', [
            'tujuan' => $tujuan,
            'pesan' => $pesan,
        ]);

        return true;
    }
}
