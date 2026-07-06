<?php

namespace App\Http\Controllers;

use App\Exports\AssetLoanExport;
use App\Models\AssetLoan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(): Response
    {
        $loans = AssetLoan::with(['employee', 'largeAsset', 'diketahuiOleh', 'disetujuiOleh'])
            ->when(request('search'), fn($q) => $q
                ->whereHas('employee', fn($q) => $q->where('nama', 'like', '%' . request('search') . '%'))
                ->orWhereHas('largeAsset', fn($q) => $q->where('nama_barang', 'like', '%' . request('search') . '%'))
            )
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('bulan'), fn($q) => $q->whereMonth('tanggal_mulai', request('bulan')))
            ->when(request('tahun'), fn($q) => $q->whereYear('tanggal_mulai', request('tahun')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Reports/Index', [
            'loans'   => $loans,
            'filters' => request()->only('search', 'status', 'bulan', 'tahun'),
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->only('search', 'status', 'bulan', 'tahun');
        $filename = 'laporan-peminjaman-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new AssetLoanExport($filters), $filename);
    }
}