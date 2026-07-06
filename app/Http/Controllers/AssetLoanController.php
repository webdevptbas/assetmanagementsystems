<?php

namespace App\Http\Controllers;

use App\Models\AssetLoan;
use App\Models\Employee;
use App\Models\LargeAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AssetLoanController extends Controller
{
    public function index(): Response
    {
        $loans = AssetLoan::with(['employee', 'largeAsset.assetType', 'diketahuiOleh', 'disetujuiOleh'])
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

        // Kirim semua asset dengan info availability
        $largeAssets = \App\Models\LargeAsset::with(['assetType', 'activeLoans.employee'])
            ->orderBy('nama_barang')
            ->get()
            ->map(fn($asset) => [
                'id'           => $asset->id,
                'nama_barang'  => $asset->nama_barang,
                'kode_barang'  => $asset->kode_barang ?? $asset->plat_nomor,
                'tipe'         => $asset->assetType?->nama,
                'is_available' => $asset->isAvailable(),
                'dipinjam_oleh'=> $asset->activeLoans->first()?->employee?->nama,
            ]);

        return Inertia::render('AssetLoans/Index', [
            'loans'       => $loans,
            'employees'   => Employee::orderBy('nama')->get(),
            'largeAssets' => $largeAssets,
            'filters'     => request()->only('search', 'status', 'bulan', 'tahun'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'large_asset_id' => 'required|exists:large_assets,id',
            'jumlah'         => 'required|integer|min:1',
            'kondisi_barang' => 'required|string',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'nullable|date|after:tanggal_mulai',
        ]);

        // Cek apakah asset sedang dipinjam
        $activeLoan = AssetLoan::where('large_asset_id', $request->large_asset_id)
            ->where('status', 'dipinjam')
            ->first();

        if ($activeLoan) {
            return back()->withErrors([
                'large_asset_id' => 'Asset ini sedang dipinjam oleh ' . $activeLoan->employee?->nama . '.',
            ]);
        }

        $asset = \App\Models\LargeAsset::find($request->large_asset_id);

        AssetLoan::create([
            'employee_id'    => $request->employee_id,
            'large_asset_id' => $request->large_asset_id,
            'nama_barang'    => $asset->nama_barang,
            'jumlah'         => $request->jumlah,
            'kondisi_barang' => $request->kondisi_barang,
            'tanggal_mulai'  => $request->tanggal_mulai,
            'tanggal_selesai'=> $request->tanggal_selesai,
            'status'         => 'dipinjam',
        ]);

        return back()->with('success', 'Peminjaman berhasil ditambahkan.');
    }

    public function update(Request $request, AssetLoan $assetLoan)
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'nama_barang'    => 'required|string',
            'jumlah'         => 'required|integer|min:1',
            'kondisi_barang' => 'required|string',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'required|date|after:tanggal_mulai',
        ]);

        $assetLoan->update($request->only([
            'employee_id', 'nama_barang', 'jumlah',
            'kondisi_barang', 'tanggal_mulai', 'tanggal_selesai',
        ]));

        return back()->with('success', 'Peminjaman berhasil diupdate.');
    }

    public function destroy(AssetLoan $assetLoan)
    {
        $assetLoan->delete();
        return back()->with('success', 'Peminjaman berhasil dihapus.');
    }

    // Approval HRD
    public function approve(AssetLoan $assetLoan)
    {
        $user = Auth::user();

        if ($user->hasRole('hrd')) {
            if ($assetLoan->diketahui_oleh) {
                return back()->withErrors(['error' => 'Sudah diketahui sebelumnya.']);
            }
            $assetLoan->update(['diketahui_oleh' => $user->id]);
            return back()->with('success', 'Peminjaman telah diketahui.');
        }

        if ($user->hasRole('manager')) {
            if (!$assetLoan->diketahui_oleh) {
                return back()->withErrors(['error' => 'HRD belum mengetahui peminjaman ini.']);
            }
            if ($assetLoan->disetujui_oleh) {
                return back()->withErrors(['error' => 'Sudah disetujui sebelumnya.']);
            }
            $assetLoan->update(['disetujui_oleh' => $user->id]);
            return back()->with('success', 'Peminjaman telah disetujui.');
        }

        return back()->withErrors(['error' => 'Tidak memiliki akses approval.']);
    }

    // Kembalikan asset
    public function kembalikan(Request $request, AssetLoan $assetLoan)
    {
        $request->validate([
            'tanggal_selesai' => 'required|date',
        ]);

        $updated = $assetLoan->update([
            'status'          => 'dikembalikan',
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        \Log::info('Kembalikan asset', [
            'id'              => $assetLoan->id,
            'tanggal_selesai' => $request->tanggal_selesai,
            'updated'         => $updated,
            'result'          => $assetLoan->fresh()->tanggal_selesai,
        ]);

        return back()->with('success', 'Asset berhasil dikembalikan.');
    }
}