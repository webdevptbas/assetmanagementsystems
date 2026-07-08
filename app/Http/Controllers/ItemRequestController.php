<?php

namespace App\Http\Controllers;

use App\Models\ItemRequest;
use App\Models\SmallAsset;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemRequestExport;

class ItemRequestController extends Controller
{
    public function index(): Response
    {
        $requests = ItemRequest::with(['employee', 'item'])
            ->when(request('search'), fn($q) => $q
                ->whereHas('employee', fn($q) => $q->where('nama', 'like', '%' . request('search') . '%'))
                ->orWhereHas('item', fn($q) => $q->where('nama_barang', 'like', '%' . request('search') . '%'))
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ItemRequests/Index', [
            'requests'  => $requests,
            'employees' => Employee::orderBy('nama')->get(),
            'items'     => SmallAsset::where('stok', '>', 0)->orderBy('nama_barang')->get(),
            'filters'   => request()->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'inventory_item_id' => 'required|exists:small_assets,id',
            'jumlah'            => 'required|integer|min:1',
        ]);

        $item = SmallAsset::find($request->inventory_item_id);

        // Hitung jumlah dalam pcs
        $jumlahPcs = $request->jumlah;

        if ($item->stok < $jumlahPcs) {
            $tersedia = $item->satuan === 'box' && $item->pcs_per_box ? floor($item->stok / $item->pcs_per_box) . ' box (' . $item->stok . ' pcs)'
                : $item->stok;

            return back()->withErrors([
                'jumlah' => 'Stok tidak mencukupi. Stok tersedia: ' . $tersedia,
            ]);
        }

        ItemRequest::create([
            'employee_id'       => $request->employee_id,
            'inventory_item_id' => $request->inventory_item_id,
            'jumlah'            => $request->jumlah,
        ]);

        // Kurangi stok dalam pcs
        $item->decrement('stok', $jumlahPcs);

        return back()->with('success', 'Permintaan barang berhasil ditambahkan.');
    }

    public function destroy(ItemRequest $itemRequest)
    {
        $item = $itemRequest->item;
        
        // Kembalikan stok dalam pcs
        $jumlahPcs = $itemRequest->jumlah;
        if ($item->satuan === 'box' && $item->pcs_per_box) {
            $jumlahPcs = $itemRequest->jumlah * $item->pcs_per_box;
        }

        $item->increment('stok', $jumlahPcs);
        $itemRequest->delete();

        return back()->with('success', 'Permintaan berhasil dihapus.');
    }

    public function export(Request $request){
        $filters = $request->only('search');
        $filename = 'laporan-permintaan-barang-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new ItemRequestExport($filters), $filename);
    }
}