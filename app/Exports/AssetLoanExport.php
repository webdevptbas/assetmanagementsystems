<?php

namespace App\Exports;

use App\Models\AssetLoan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetLoanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return AssetLoan::with(['employee', 'largeAsset', 'diketahuiOleh', 'disetujuiOleh'])
            ->when($this->filters['search'] ?? null, fn($q) => $q
                ->whereHas('employee', fn($q) => $q->where('nama', 'like', '%' . $this->filters['search'] . '%'))
                ->orWhereHas('largeAsset', fn($q) => $q->where('nama_barang', 'like', '%' . $this->filters['search'] . '%'))
            )
            ->when($this->filters['status'] ?? null, fn($q) => $q->where('status', $this->filters['status']))
            ->when($this->filters['bulan'] ?? null, fn($q) => $q->whereMonth('tanggal_mulai', $this->filters['bulan']))
            ->when($this->filters['tahun'] ?? null, fn($q) => $q->whereYear('tanggal_mulai', $this->filters['tahun']))
            ->oldest();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Peminjam',
            'NIK',
            'Jabatan',
            'Divisi',
            'Nama Barang',
            'Kode Barang',
            'Jumlah',
            'Kondisi Barang',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Diketahui Oleh',
            'Disetujui Oleh',
            'Status',
        ];
    }

    public function map($loan): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $loan->employee?->nama ?? '-',
            $loan->employee?->nik ?? '-',
            $loan->employee?->jabatan ?? '-',
            $loan->employee?->divisi ?? '-',
            $loan->largeAsset?->nama_barang ?? $loan->nama_barang,
            $loan->largeAsset?->kode_barang ?? '-',
            $loan->jumlah,
            $loan->kondisi_barang,
            $loan->tanggal_mulai ? $loan->tanggal_mulai->format('d/m/Y') : '-',
            $loan->tanggal_selesai ? $loan->tanggal_selesai->format('d/m/Y') : '-',
            $loan->diketahuiOleh?->name ?? '-',
            $loan->disetujuiOleh?->name ?? '-',
            $loan->status === 'dikembalikan' ? 'Dikembalikan' : 'Dipinjam',
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