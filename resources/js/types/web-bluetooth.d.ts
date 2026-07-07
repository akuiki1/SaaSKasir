// Deklarasi minimal Web Bluetooth API — TIDAK ada di lib DOM standar TypeScript,
// jadi `navigator.bluetooth` akan error tanpa ini. Hanya subset yang dipakai
// `@/lib/thermalPrinter`. Spesifikasi: https://webbluetoothcg.github.io/web-bluetooth/

type BluetoothServiceUUID = number | string;
type BluetoothCharacteristicUUID = number | string;

interface BluetoothCharacteristicProperties {
    readonly write: boolean;
    readonly writeWithoutResponse: boolean;
}

interface BluetoothRemoteGATTCharacteristic {
    readonly uuid: string;
    readonly properties: BluetoothCharacteristicProperties;
    writeValue(value: BufferSource): Promise<void>;
    writeValueWithoutResponse(value: BufferSource): Promise<void>;
}

interface BluetoothRemoteGATTService {
    readonly uuid: string;
    getCharacteristic(
        characteristic: BluetoothCharacteristicUUID,
    ): Promise<BluetoothRemoteGATTCharacteristic>;
    getCharacteristics(): Promise<BluetoothRemoteGATTCharacteristic[]>;
}

interface BluetoothRemoteGATTServer {
    readonly connected: boolean;
    connect(): Promise<BluetoothRemoteGATTServer>;
    disconnect(): void;
    getPrimaryService(
        service: BluetoothServiceUUID,
    ): Promise<BluetoothRemoteGATTService>;
    getPrimaryServices(): Promise<BluetoothRemoteGATTService[]>;
}

interface BluetoothDevice extends EventTarget {
    readonly id: string;
    readonly name?: string;
    readonly gatt?: BluetoothRemoteGATTServer;
}

interface RequestDeviceOptions {
    filters?: Array<{
        services?: BluetoothServiceUUID[];
        name?: string;
        namePrefix?: string;
    }>;
    optionalServices?: BluetoothServiceUUID[];
    acceptAllDevices?: boolean;
}

interface Bluetooth {
    getAvailability(): Promise<boolean>;
    requestDevice(options?: RequestDeviceOptions): Promise<BluetoothDevice>;
    getDevices?(): Promise<BluetoothDevice[]>;
}

interface Navigator {
    readonly bluetooth?: Bluetooth;
}
