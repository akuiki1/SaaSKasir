import { buildReceiptBytes } from '@/lib/escpos';
import type { StrukData } from '@/lib/struk';

/**
 * Cetak struk langsung ke printer thermal Bluetooth (BLE) via Web Bluetooth API,
 * mengirim byte ESC/POS dari `@/lib/escpos`.
 *
 * BATASAN (sengaja gagal-lunak, halaman kasir tetap punya fallback window.print):
 * - Hanya Chrome/Edge (Android & desktop). iOS Safari TIDAK mendukung Web Bluetooth.
 * - Wajib secure context (HTTPS atau localhost). Di http biasa `navigator.bluetooth`
 *   bahkan tidak ada — sama seperti kendala service worker PWA.
 *
 * Device yang tersambung diingat selama sesi (singleton modul) supaya cetak
 * berikutnya tak perlu dialog pilih perangkat lagi.
 */

/** Pasangan service/karakteristik printer thermal 58mm murah yang umum di pasaran. */
interface PrinterProfile {
    service: string;
    characteristic: string;
}

// Web Bluetooth mewajibkan setiap service didaftarkan di `optionalServices`
// saat requestDevice, kalau tidak getPrimaryService akan ditolak keamanan.
const KNOWN_PROFILES: PrinterProfile[] = [
    // Goojprt / MTP-II / mayoritas printer generik "PT-2xx".
    {
        service: '000018f0-0000-1000-8000-00805f9b34fb',
        characteristic: '00002af1-0000-1000-8000-00805f9b34fb',
    },
    // Sejumlah printer "FF00".
    {
        service: '0000ff00-0000-1000-8000-00805f9b34fb',
        characteristic: '0000ff02-0000-1000-8000-00805f9b34fb',
    },
    // Modul BLE HM-10/serupa (FFE0).
    {
        service: '0000ffe0-0000-1000-8000-00805f9b34fb',
        characteristic: '0000ffe1-0000-1000-8000-00805f9b34fb',
    },
    // ISSC / Microchip transparent UART.
    {
        service: '49535343-fe7d-4ae5-8fa9-9fafd205e455',
        characteristic: '49535343-8841-43f4-a8d4-ecbe34729bb3',
    },
];

const KNOWN_SERVICES = KNOWN_PROFILES.map((profile) => profile.service);

/** BLE MTU kecil; potong data ke blok aman agar tidak terpotong/hilang. */
const CHUNK_SIZE = 180;

/** Jeda antar-blok (ms) supaya buffer printer tidak overflow. */
const CHUNK_DELAY = 20;

let device: BluetoothDevice | null = null;
let characteristic: BluetoothRemoteGATTCharacteristic | null = null;

/** Apakah perangkat & konteks ini mendukung cetak Bluetooth sama sekali. */
export function bluetoothPrintingSupported(): boolean {
    return typeof navigator !== 'undefined' && !!navigator.bluetooth;
}

/** Nama printer yang sedang tersambung, atau null bila belum/terputus. */
export function connectedPrinterName(): string | null {
    if (device?.gatt?.connected) {
        return device.name ?? 'Printer';
    }

    return null;
}

/** Buka dialog pilih perangkat & sambungkan. Mengembalikan nama printer. */
export async function connectPrinter(): Promise<string> {
    if (!bluetoothPrintingSupported()) {
        throw new Error(
            'Perangkat ini tidak mendukung cetak Bluetooth (butuh Chrome di Android/desktop & HTTPS).',
        );
    }

    const chosen = await navigator.bluetooth!.requestDevice({
        acceptAllDevices: true,
        optionalServices: KNOWN_SERVICES,
    });

    chosen.addEventListener('gattserverdisconnected', () => {
        characteristic = null;
    });

    device = chosen;
    characteristic = await resolveCharacteristic(chosen);

    return chosen.name ?? 'Printer';
}

/** Putuskan printer & lupakan (dipakai tombol "ganti printer"). */
export function disconnectPrinter(): void {
    if (device?.gatt?.connected) {
        device.gatt.disconnect();
    }

    device = null;
    characteristic = null;
}

/**
 * Cetak struk. Bila belum ada printer terpilih, membuka dialog; bila sudah
 * pernah tersambung tapi GATT terputus, menyambung ulang tanpa dialog.
 */
export async function printReceiptBluetooth(
    trx: StrukData,
    namaToko: string,
): Promise<void> {
    const target = await ensureCharacteristic();

    await writeInChunks(target, buildReceiptBytes(trx, namaToko));
}

async function ensureCharacteristic(): Promise<BluetoothRemoteGATTCharacteristic> {
    if (characteristic && device?.gatt?.connected) {
        return characteristic;
    }

    if (!device) {
        await connectPrinter();

        return characteristic!;
    }

    // Perangkat dikenal tapi GATT terputus → sambung ulang tanpa dialog baru.
    characteristic = await resolveCharacteristic(device);

    return characteristic;
}

async function resolveCharacteristic(
    dev: BluetoothDevice,
): Promise<BluetoothRemoteGATTCharacteristic> {
    if (!dev.gatt) {
        throw new Error('Printer tidak memiliki antarmuka GATT.');
    }

    const server = dev.gatt.connected ? dev.gatt : await dev.gatt.connect();

    // 1) Coba profil yang dikenal — paling cepat & andal.
    for (const profile of KNOWN_PROFILES) {
        try {
            const service = await server.getPrimaryService(profile.service);

            return await service.getCharacteristic(profile.characteristic);
        } catch {
            // Profil ini tidak ada di printer; coba berikutnya.
            continue;
        }
    }

    // 2) Fallback: pindai semua service, ambil karakteristik writable pertama.
    const services = await server.getPrimaryServices();

    for (const service of services) {
        const chars = await service.getCharacteristics();

        for (const char of chars) {
            if (char.properties.write || char.properties.writeWithoutResponse) {
                return char;
            }
        }
    }

    throw new Error('Tidak menemukan karakteristik printer yang bisa ditulis.');
}

async function writeInChunks(
    char: BluetoothRemoteGATTCharacteristic,
    bytes: Uint8Array,
): Promise<void> {
    const withoutResponse = char.properties.writeWithoutResponse;

    for (let offset = 0; offset < bytes.length; offset += CHUNK_SIZE) {
        const chunk = bytes.slice(offset, offset + CHUNK_SIZE);

        if (withoutResponse) {
            await char.writeValueWithoutResponse(chunk);
        } else {
            await char.writeValue(chunk);
        }

        await delay(CHUNK_DELAY);
    }
}

function delay(ms: number): Promise<void> {
    return new Promise((resolve) => {
        setTimeout(resolve, ms);
    });
}
