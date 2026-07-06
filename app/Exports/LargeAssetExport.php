<?php

namespace App\Exports;

use App\Models\LargeAsset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LargeAssetExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return LargeAsset::with('assetType')->oldest();
    }

    public function headings(): array
    {
        return [
            'No', 'Kode Barang', 'Nama Barang', 'Tipe Asset',
            'Tgl Pembelian', 'Garansi', 'Akhir Garansi', 'Serial Number', 'Note',
        ];
    }

    public function map($asset): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $asset->kode_barang ?? $asset->plat_nomor ?? '-',
            $asset->nama_barang,
            $asset->assetType?->nama ?? '-',
            $asset->tanggal_pembelian?->format('d/m/Y') ?? '-',
            $asset->garansi ? 'Ya' : 'Tidak',
            $asset->warranty_end_date?->format('d/m/Y') ?? '-',
            $asset->serial_number ?? '-',
            $asset->note,
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