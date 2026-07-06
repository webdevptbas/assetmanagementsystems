<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use App\Models\SmallAssetType;
use App\Models\LargeAsset;
use App\Models\SmallAsset;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LargeAssetExport;
use App\Exports\SmallAssetExport;

class InventoryController extends Controller
{
    public function index(): Response
    {
        $tab = request('tab', 'large');

        $largeAssets = LargeAsset::with('assetType')
        ->when(request('search'), fn($q) => $q
            ->where('nama_barang', 'like', '%' . request('search') . '%')
            ->orWhere('kode_barang', 'like', '%' . request('search') . '%')
        )
        ->oldest()
        ->paginate(10)
        ->withQueryString();

    $smallAssets = SmallAsset::with('assetType')
        ->when(request('search'), fn($q) => $q
            ->where('nama_barang', 'like', '%' . request('search') . '%')
            ->orWhere('kode_barang', 'like', '%' . request('search') . '%')
        )
        ->oldest()
        ->paginate(10)
        ->withQueryString();

        return Inertia::render('Inventory/Index', [
            'tab'             => $tab,
            'largeAssets'     => $largeAssets,
            'smallAssets'     => $smallAssets,
            'assetTypes'      => AssetType::withCount('largeAssets')->orderBy('nama')->get(),
            'smallAssetTypes' => SmallAssetType::orderBy('nama')->get(),
            'filters'         => request()->only('search', 'tab'),
        ]);
    }

    public function storeAssetType(Request $request)
    {
        $request->validate(['nama' => 'required|string|unique:asset_types,nama']);
        AssetType::create($request->only('nama'));
        return back()->with('success', 'Tipe asset berhasil ditambahkan.');
    }

    public function updateAssetType(Request $request, AssetType $assetType)
    {
        $request->validate(['nama' => 'required|string|unique:asset_types,nama,' . $assetType->id]);
        $assetType->update($request->only('nama'));
        return back()->with('success', 'Tipe asset berhasil diupdate.');
    }

    public function destroyAssetType(AssetType $assetType)
    {
        $assetType->delete();
        return back()->with('success', 'Tipe asset berhasil dihapus.');
    }

    public function storeSmallAssetType(Request $request)
    {
        $request->validate(['nama' => 'required|string|unique:small_asset_types,nama']);
        SmallAssetType::create($request->only('nama'));
        return back()->with('success', 'Tipe asset kecil berhasil ditambahkan.');
    }

    public function updateSmallAssetType(Request $request, SmallAssetType $smallAssetType)
    {
        $request->validate(['nama' => 'required|string|unique:small_asset_types,nama,' . $smallAssetType->id]);
        $smallAssetType->update($request->only('nama'));
        return back()->with('success', 'Tipe asset kecil berhasil diupdate.');
    }

    public function destroySmallAssetType(SmallAssetType $smallAssetType)
    {
        $smallAssetType->delete();
        return back()->with('success', 'Tipe asset kecil berhasil dihapus.');
    }

    public function storeLargeAsset(Request $request)
    {
        $request->validate([
            'nama_barang'       => 'required|string',
            'asset_type_id'     => 'required|exists:asset_types,id',
            'tanggal_pembelian' => 'required|date',
            'garansi'           => 'required|boolean',
            'warranty_end_date' => 'nullable|date|required_if:garansi,true',
            'serial_number'     => 'nullable|numeric',
            'note'              => 'required|string',
            'plat_nomor'        => 'nullable|string',
        ]);

        $assetType = AssetType::find($request->asset_type_id);
        $isVehicle = in_array(strtolower($assetType->nama), ['mobil', 'motor']);

        $data = $request->only([
            'nama_barang', 'asset_type_id', 'tanggal_pembelian',
            'garansi', 'warranty_end_date', 'serial_number', 'note',
        ]);

        if ($isVehicle) {
            $data['plat_nomor'] = $request->plat_nomor;
            $data['kode_barang'] = null;
        } else {
            $data['kode_barang'] = LargeAsset::generateKode();
            $data['plat_nomor'] = null;
        }

        LargeAsset::create($data);
        return back()->with('success', 'Asset berhasil ditambahkan.');
    }

    public function updateLargeAsset(Request $request, LargeAsset $largeAsset)
    {
        $request->validate([
            'nama_barang'       => 'required|string',
            'asset_type_id'     => 'required|exists:asset_types,id',
            'tanggal_pembelian' => 'required|date',
            'garansi'           => 'required|boolean',
            'warranty_end_date' => 'nullable|date|required_if:garansi,true',
            'serial_number'     => 'nullable|numeric',
            'note'              => 'required|string',
            'plat_nomor'        => 'nullable|string',
        ]);

        $assetType = AssetType::find($request->asset_type_id);
        $isVehicle = in_array(strtolower($assetType->nama), ['mobil', 'motor']);

        $data = $request->only([
            'nama_barang', 'asset_type_id', 'tanggal_pembelian',
            'garansi', 'warranty_end_date', 'serial_number', 'note',
        ]);

        if ($isVehicle) {
            $data['plat_nomor'] = $request->plat_nomor;
            $data['kode_barang'] = null;
        } else {
            $data['plat_nomor'] = null;
        }

        $largeAsset->update($data);
        return back()->with('success', 'Asset berhasil diupdate.');
    }

    public function destroyLargeAsset(LargeAsset $largeAsset)
    {
        $largeAsset->delete();
        return back()->with('success', 'Asset berhasil dihapus.');
    }

    public function storeSmallAsset(Request $request)
    {
        $request->validate([
            'nama_barang'         => 'required|string',
            'small_asset_type_id' => 'required|exists:small_asset_types,id',
            'stok'                => 'required|integer|min:0',
            'satuan'              => 'required|string',
            'pcs_per_box'         => 'nullable|integer|min:1',
            'keterangan'          => 'nullable|string',
        ]);

        $data = $request->only(['nama_barang', 'small_asset_type_id', 'stok', 'satuan', 'pcs_per_box', 'keterangan']);
        $data['kode_barang'] = SmallAsset::generateKode();
        SmallAsset::create($data);
        return back()->with('success', 'Asset kecil berhasil ditambahkan.');
    }

    public function updateSmallAsset(Request $request, SmallAsset $smallAsset)
    {
        $request->validate([
            'nama_barang'         => 'required|string',
            'small_asset_type_id' => 'required|exists:small_asset_types,id',
            'stok'                => 'required|integer|min:0',
            'satuan'              => 'required|string',
            'pcs_per_box'         => 'nullable|integer|min:1',
            'keterangan'          => 'nullable|string',
        ]);

        $smallAsset->update($request->only(['nama_barang', 'small_asset_type_id', 'stok', 'satuan', 'pcs_per_box', 'keterangan']));
        return back()->with('success', 'Asset kecil berhasil diupdate.');
    }

    public function destroySmallAsset(SmallAsset $smallAsset)
    {
        $smallAsset->delete();
        return back()->with('success', 'Asset kecil berhasil dihapus.');
    }

    public function restock(Request $request, SmallAsset $smallAsset)
    {
        $request->validate(['jumlah' => 'required|integer|min:1']);
        $smallAsset->increment('stok', $request->jumlah);
        return back()->with('success', 'Stok berhasil ditambahkan.');
    }

    public function exportLarge(){
        return Excel::download(new LargeAssetExport(), 'laporan-asset-besar-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function exportSmall(){
        return Excel::download(new SmallAssetExport(), 'laporan-asset-kecil-' . now()->format('Ymd-His') . '.xlsx');
    }
}