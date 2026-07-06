<?php

namespace App\Exports;

use App\Models\ItemRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemRequestExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return ItemRequest::with(['employee', 'item'])
            ->when($this->filters['search'] ?? null, fn($q) => $q
                ->whereHas('employee', fn($q) => $q->where('nama', 'like', '%' . $this->filters['search'] . '%'))
                ->orWhereHas('item', fn($q) => $q->where('nama_barang', 'like', '%' . $this->filters['search'] . '%'))
            )
            ->oldest();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Peminta',
            'NIK',
            'Jabatan',
            'Divisi',
            'Kode Barang',
            'Nama Barang',
            'Jumlah',
            'Sisa Stok',
            'Tanggal',
        ];
    }

    public function map($req): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $req->employee?->nama ?? '-',
            $req->employee?->nik ?? '-',
            $req->employee?->jabatan ?? '-',
            $req->employee?->divisi ?? '-',
            $req->item?->kode_barang ?? '-',
            $req->item?->nama_barang ?? '-',
            $req->jumlah,
            $req->item?->stok ?? 0,
            $req->created_at?->format('d/m/Y') ?? '-',
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