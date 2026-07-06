<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
        public function index(): Response
    {
        $employees = Employee::query()
            ->when(request('search'), fn($q) => $q
                ->where('nama', 'like', '%' . request('search') . '%')
                ->orWhere('nik', 'like', '%' . request('search') . '%')
            )
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $divisions = \App\Models\Division::orderBy('nama')->get();
        $positions = \App\Models\Position::orderBy('nama')->get();

        // Ambil employee_id yang sudah pernah transfer
        $transferredIds = \App\Models\EmployeeTransfer::pluck('employee_id')->unique()->toArray();

        return Inertia::render('Employees/Index', [
            'employees'      => $employees,
            'filters'        => request()->only('search'),
            'divisions'      => $divisions,
            'positions'      => $positions,
            'transferred_ids' => $transferredIds,
        ]);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|numeric|unique:employees,nik',
            'jabatan' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_bpjs_ketenagakerjaan' => 'nullable|string|max:255',
            'no_bpjs_kesehatan' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')){
            $validated['foto'] = $request->file('foto')->store('employees', 'public');
        }

        Employee::create($validated);
        return back()->with('success', 'Data Karyawan Berhasil Ditambahkan!');
    }

    public function update(Request $request, Employee $employee){
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => ['required', 'numeric', Rule::unique('employees', 'nik')->ignore($employee->id)],
            'jabatan' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_bpjs_ketenagakerjaan' => 'nullable|string|max:255',
            'no_bpjs_kesehatan' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')){
            if($employee->foto){
                \Storage::disk('public')->delete($employee->foto);
            }
            $validated['foto'] = $request->file('foto')->store('employees', 'public');
        }else{
            unset($validated['foto']);
        }

        $employee->update($validated);
        return back()->with('success', 'Data Karyawan Berhasil Diupdate!');
    }

    public function destroy(Employee $employee){
        $employee->delete();
        return back()->with('success', 'Data Karyawan Berhasil Dihapus!');
    }

}
