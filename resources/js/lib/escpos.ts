import { formatNumber, formatRupiah } from '@/lib/format';
import type { StrukData } from '@/lib/struk';

// Encoder perintah ESC/POS untuk printer thermal 58mm (Font A = 32 karakter/baris).
// Mencerminkan isi struk HTML di `@/lib/struk` (buildReceiptHtml) — termasuk
// titipan jasa & catatan "Hemat" promo — tapi dalam byte mentah yang dikirim
// langsung ke printer via Web Bluetooth (`@/lib/thermalPrinter`).

/** Lebar kertas 58mm dalam karakter pada ukuran normal. */
const WIDTH = 32;

/** Lebar efektif saat teks digandakan (double width) — dipakai judul toko. */
const WIDTH_DOUBLE = 16;

const ESC = 0x1b;
const GS = 0x1d;
const LF = 0x0a;

/** Batas atas rentang combining diacritical marks (U+0300–U+036F). */
const COMBINING_START = 0x300;
const COMBINING_END = 0x36f;

/** Perakit byte ESC/POS berantai. */
class EscPos {
    private bytes: number[] = [];

    private push(...b: number[]): this {
        for (const byte of b) {
            this.bytes.push(byte);
        }

        return this;
    }

    /** ESC @ — reset printer ke kondisi awal. */
    init(): this {
        return this.push(ESC, 0x40);
    }

    /** ESC a n — perataan (0 kiri, 1 tengah, 2 kanan). */
    align(mode: 'left' | 'center' | 'right'): this {
        const n = mode === 'center' ? 1 : mode === 'right' ? 2 : 0;

        return this.push(ESC, 0x61, n);
    }

    /** ESC E n — tebal on/off. */
    bold(on: boolean): this {
        return this.push(ESC, 0x45, on ? 1 : 0);
    }

    /** GS ! n — ukuran karakter (double width+height bila on). */
    double(on: boolean): this {
        return this.push(GS, 0x21, on ? 0x11 : 0x00);
    }

    /** Kirim teks (ASCII; non-ASCII di-fold agar tidak jadi glyph acak). */
    text(value: string): this {
        return this.push(...encodeText(value));
    }

    /** Teks + line feed. */
    line(value = ''): this {
        return this.text(value).push(LF);
    }

    /** ESC d n — maju n baris. */
    feed(n = 1): this {
        return this.push(ESC, 0x64, n);
    }

    /** GS V m — potong kertas (aman diabaikan printer tanpa cutter). */
    cut(): this {
        return this.push(GS, 0x56, 0x00);
    }

    build(): Uint8Array {
        return Uint8Array.from(this.bytes);
    }
}

/**
 * Fold ke ASCII: pecah aksen via NFKD lalu buang tanda diakritik (é→e),
 * karakter non-ASCII yang tersisa jadi '?' agar tidak tercetak jadi glyph acak.
 */
function encodeText(value: string): number[] {
    const out: number[] = [];

    for (const ch of value.normalize('NFKD')) {
        const code = ch.codePointAt(0) ?? 0x3f;

        if (code >= COMBINING_START && code <= COMBINING_END) {
            continue;
        }

        out.push(code <= 0x7f ? code : 0x3f);
    }

    return out;
}

/** Susun "kiri ........ kanan" selebar `width`; potong kiri bila mepet. */
function twoCol(left: string, right: string, width = WIDTH): string {
    const gap = width - left.length - right.length;

    if (gap >= 1) {
        return left + ' '.repeat(gap) + right;
    }

    const maxLeft = Math.max(0, width - right.length - 1);

    return left.slice(0, maxLeft) + ' ' + right;
}

/** Bungkus teks panjang menjadi beberapa baris <= `width` karakter. */
function wrapText(text: string, width = WIDTH): string[] {
    const lines: string[] = [];
    let current = '';

    for (const word of text.split(/\s+/).filter(Boolean)) {
        const candidate = current ? `${current} ${word}` : word;

        if (candidate.length <= width) {
            current = candidate;

            continue;
        }

        if (current) {
            lines.push(current);
        }

        // Kata tunggal lebih panjang dari lebar → pecah paksa.
        let rest = word;

        while (rest.length > width) {
            lines.push(rest.slice(0, width));
            rest = rest.slice(width);
        }

        current = rest;
    }

    if (current) {
        lines.push(current);
    }

    return lines.length > 0 ? lines : [''];
}

/**
 * Rangkai struk transaksi final menjadi byte ESC/POS siap kirim ke printer.
 * `namaToko` dari model Toko aktif (jangan hardcode — lihat aturan multi-tenant).
 */
export function buildReceiptBytes(
    trx: StrukData,
    namaToko: string,
): Uint8Array {
    const p = new EscPos();
    const divider = '-'.repeat(WIDTH);

    p.init();

    // Kepala: nama toko besar + label.
    p.align('center').bold(true).double(true);

    for (const baris of wrapText(namaToko, WIDTH_DOUBLE)) {
        p.line(baris);
    }

    p.double(false).bold(false);
    p.line('Struk Transaksi');
    p.align('left').line(divider);

    p.line(`Kode: ${trx.kode}`);
    p.line(`${trx.tanggal} ${trx.waktu}`);
    p.line(divider);

    // Baris belanja: nama (bisa multi-baris), lalu "qty x harga .... subtotal".
    let titipan = 0;

    for (const detail of trx.details) {
        titipan += detail.nominal ? Number(detail.nominal) : 0;

        for (const baris of wrapText(detail.nama_produk)) {
            p.line(baris);
        }

        const qtyHarga = `  ${formatNumber(detail.jumlah)} x ${formatRupiah(detail.harga)}`;

        p.line(twoCol(qtyHarga, formatRupiah(detail.subtotal)));

        const hemat = detail.harga * detail.jumlah - detail.subtotal;

        if (hemat >= 1) {
            p.line(`  Hemat ${formatRupiah(hemat)}`);
        }
    }

    p.line(divider);

    // Total: titipan jasa (transfer/tarik) ikut ditagih tunai walau bukan omzet.
    const tagihan = trx.total_harga + titipan;

    if (titipan > 0) {
        p.line(twoCol('Penjualan / Fee', formatRupiah(trx.total_harga)));
        p.line(twoCol('Titipan layanan', formatRupiah(titipan)));
    }

    p.bold(true)
        .line(twoCol('Total', formatRupiah(tagihan)))
        .bold(false);
    p.line(twoCol('Bayar', formatRupiah(trx.bayar)));
    p.line(twoCol('Kembalian', formatRupiah(trx.kembalian)));
    p.line(divider);

    const diskon = Number(trx.diskon ?? 0);

    p.align('center');

    if (diskon > 0) {
        p.line(`Anda hemat ${formatRupiah(diskon)} dari promo.`);
    }

    p.line('Terima kasih atas');
    p.line('kunjungan Anda.');

    p.feed(3).cut();

    return p.build();
}
