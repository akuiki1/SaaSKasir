<?php

namespace App\Services\Whatsapp;

use App\Contracts\WhatsappSender;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Driver WhatsApp lewat gateway HTTP lokal (dibentuk mengikuti Fonnte:
 * POST {target, message} + header Authorization: <token>). Gateway sejenis
 * (Wablas/Watzap) berbagi bentuk serupa — sesuaikan field bila ganti provider.
 *
 * SEAM: tetap AMAN saat endpoint/token belum diisi — kalau salah satu kosong,
 * kirim() langsung mengembalikan false tanpa panggilan HTTP (tak ada efek di
 * dev/CI). Baru aktif saat WHATSAPP_ENDPOINT + WHATSAPP_TOKEN di-set & driver
 * diubah ke 'gateway'.
 */
class GatewayWhatsappSender implements WhatsappSender
{
    public function __construct(
        private readonly ?string $endpoint,
        private readonly ?string $token,
    ) {}

    public function kirim(string $tujuan, string $pesan): bool
    {
        if (empty($this->endpoint) || empty($this->token)) {
            Log::warning('[WA] driver gateway dipilih tapi endpoint/token belum di-set — pesan tidak dikirim.', [
                'tujuan' => $tujuan,
            ]);

            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $this->token])
                ->asForm()
                ->timeout(15)
                ->post($this->endpoint, [
                    'target' => $tujuan,
                    'message' => $pesan,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('[WA] gateway menolak pengiriman.', [
                'tujuan' => $tujuan,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('[WA] gagal menghubungi gateway.', [
                'tujuan' => $tujuan,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
