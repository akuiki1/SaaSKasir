<?php

namespace App\Contracts;

/**
 * Abstraksi pengirim WhatsApp (SEAM). Implementasi konkret dipilih lewat
 * config('services.whatsapp.driver') & di-bind di AppServiceProvider:
 *
 * - 'log'     → LogWhatsappSender (default): tulis pesan ke log, gratis, tak
 *               butuh kredensial — dipakai dev/CI dan sampai gateway live siap.
 * - 'gateway' → GatewayWhatsappSender: HTTP POST ke gateway lokal (Fonnte dsb).
 *
 * Semua pemanggil (mis. KirimLaporanHarian) bergantung ke interface ini, bukan
 * ke gateway tertentu — sama seperti seam Midtrans di billing.
 */
interface WhatsappSender
{
    /**
     * Kirim satu pesan teks ke satu nomor.
     *
     * @param  string  $tujuan  Nomor tujuan format 62xxxxxxxxxx (sudah dinormalisasi).
     * @param  string  $pesan  Isi pesan (boleh multi-baris, markdown WA).
     * @return bool True bila terkirim/diterima gateway; false bila gagal (pemanggil
     *              yang memutuskan retry/log — pengiriman TIDAK melempar exception).
     */
    public function kirim(string $tujuan, string $pesan): bool;
}
