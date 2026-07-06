<?php

namespace App\Http\Controllers;

use App\Models\AssetLoan;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_dipinjam'     => AssetLoan::where('status', 'dipinjam')->count(),
            'sudah_dikembalikan' => AssetLoan::where('status', 'dikembalikan')->count(),
            'belum_dikembalikan' => AssetLoan::where('status', 'dipinjam')->count(),
            'total_stock'        => \App\Models\LargeAsset::count() + \App\Models\SmallAsset::count(),
            'stock_menipis' => \App\Models\SmallAsset::where('stok', '<', 10)->count(),
        ];

        $peminjaman_terbaru = AssetLoan::with(['employee', 'largeAsset'])
            ->latest()
            ->take(5)
            ->get();

        $top_peminjam = AssetLoan::with('employee')
            ->selectRaw('employee_id, count(*) as total')
            ->groupBy('employee_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats'              => $stats,
            'peminjaman_terbaru' => $peminjaman_terbaru,
            'top_peminjam'       => $top_peminjam,
        ]);
    }
}
