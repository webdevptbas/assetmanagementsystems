<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        $request->validate(['nama' => 'required|string|unique:divisions,nama']);
        Division::create($request->only('nama'));
        return back()->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function updateDivision(Request $request, Division $division)
    {
        $request->validate(['nama' => 'required|string|unique:divisions,nama,' . $division->id]);
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
        $request->validate(['nama' => 'required|string|unique:positions,nama']);
        Position::create($request->only('nama'));
        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function updatePosition(Request $request, Position $position)
    {
        $request->validate(['nama' => 'required|string|unique:positions,nama,' . $position->id]);
        $position->update($request->only('nama'));
        return back()->with('success', 'Jabatan berhasil diupdate.');
    }

    public function destroyPosition(Position $position)
    {
        $position->delete();
        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}