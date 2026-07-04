<?php

namespace App\Services;

use App\Imports\ProdukImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Wrapper tipis di atas Excel::import() -- controller tidak perlu bergantung
 * langsung pada facade Maatwebsite\Excel.
 */
class ProdukImportService
{
    public function __construct(private readonly ProdukService $produkService) {}

    /**
     * @return array{berhasil: int, dilewati: array<int, string>}
     */
    public function importDariFile(UploadedFile $file): array
    {
        $import = new ProdukImport($this->produkService);

        Excel::import($import, $file);

        return [
            'berhasil' => $import->berhasil,
            'dilewati' => $import->dilewati,
        ];
    }
}
