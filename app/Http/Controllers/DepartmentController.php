<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(): Response
{
    return Inertia::render('Departments/Index', [
        'divisions' => Division::orderBy('nama')->get(),
        'positions' => Position::orderBy('nama')->get(),
    ]);
}

    public function storeDivision(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', Rule::unique('divisions')->whereNull('deleted_at')],
        ]);
        Division::create($request->only('nama'));
        return back()->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function updateDivision(Request $request, Division $division)
    {
        $request->validate([
            'nama' => ['required', 'string', Rule::unique('divisions')->ignore($division->id)->whereNull('deleted_at')],
        ]);
        $division->update($request->only('nama'));
        return back()->with('success', 'Divisi berhasil diupdate.');
    }

    public function destroyDivision(Division $division)
    {
        $division->delete();
        return back()->with('success', 'Divisi berhasil dihapus.');
    }

    public function storePosition(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', Rule::unique('positions')->whereNull('deleted_at')],
        ]);
        Position::create($request->only('nama'));
        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function updatePosition(Request $request, Position $position)
    {
        $request->validate([
            'nama' => ['required', 'string', Rule::unique('positions')->ignore($position->id)->whereNull('deleted_at')],
        ]);
        $position->update($request->only('nama'));
        return back()->with('success', 'Jabatan berhasil diupdate.');
    }

    public function destroyPosition(Position $position)
    {
        $position->delete();
        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}