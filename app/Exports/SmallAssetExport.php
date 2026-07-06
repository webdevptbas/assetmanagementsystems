<?php

namespace App\Exports;

use App\Models\SmallAsset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SmallAssetExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return SmallAsset::with('assetType')->oldest();
    }

    public function headings(): array
    {
        return ['No', 'Kode Barang', 'Nama Barang', 'Tipe Asset', 'Stok'];
    }

    public function map($asset): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $asset->kode_barang,
            $asset->nama_barang,
            $asset->assetType?->nama ?? '-',
            $asset->stok,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1A1A1A']],
            ],
        ];
    }
}