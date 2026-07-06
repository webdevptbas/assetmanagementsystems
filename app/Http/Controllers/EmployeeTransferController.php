<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeTransfer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeTransferController extends Controller
{
    public function index(): Response
    {
        $transfers = EmployeeTransfer::with('employee')
            ->when(request('search'), fn($q) => $q
                ->whereHas('employee', fn($q) => $q->where('nama', 'like', '%' . request('search') . '%'))
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('EmployeeTransfers/Index', [
            'transfers' => $transfers,
            'employees' => Employee::orderBy('nama')->get(),
            'divisions' => \App\Models\Division::orderBy('nama')->get(),
            'positions' => \App\Models\Position::orderBy('nama')->get(),
            'filters'   => request()->only('search'),
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'jabatan_baru' => 'required|string',
            'divisi_baru' => 'required|string',
            'tanggal_efektif' => 'required|date',
            'keterangan' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $employee = Employee::find($request->employee_id);

        //History transfer
        $data = [
            'employee_id' => $employee->id,
            'jabatan_lama' => $employee->jabatan,
            'divisi_lama' => $employee->divisi,
            'jabatan_baru' => $request->jabatan_baru,
            'divisi_baru' => $request->divisi_baru,
            'tanggal_efektif' => $request->tanggal_efektif,
            'keterangan' => $request->keterangan,
        ];

        if($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment'] = $request->file('attachment')->store('transfers', 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        EmployeeTransfer::create($data);

        //Update divisi & jabatan karyawan
        $employee->update([
            'jabatan' => $request->jabatan_baru,
            'divisi' => $request->divisi_baru,
        ]);

        return back()->with('success', 'Transfer karyawan berhasil dicatat!');
    }

    public function destroy (EmployeeTransfer $employeeTransfer){

        $employeeTransfer->employee->update([
            'jabatan' => $employeeTransfer->jabatan_lama,
            'divisi' => $employeeTransfer->divisi_lama,
        ]);

        if($employeeTransfer->attachment){
            \Storage::disk('public')->delete($employeeTransfer->attachment);
        }
        $employeeTransfer->delete();
        return back()->with('success', 'History transfer berhasil dihapus!');
    }
}
