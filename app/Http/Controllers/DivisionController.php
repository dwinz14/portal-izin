<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;

class DivisionController extends Controller
{

    /**
     * Display a listing of the divisions.
     */
    public function index(Request $request)
    {

        $divisions = Division::query()
            ->paginate(10);

        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * Show the form for creating a new division.
     */
    public function create()
    {
        return view('admin.divisions.create');
    }

    /**
     * Store a newly created division in storage.
     */
    public function store(StoreDivisionRequest $request)
    {
        $nama_divisi = strtolower(trim($request->validated('nama_divisi')));

        Division::create(['nama_divisi' => $nama_divisi]);

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Divisi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified division.
     */
    public function edit(Division $division)
    {
        // Transform nama_divisi to uppercase for display in form
        $division->nama_divisi = strtoupper($division->nama_divisi);
        return view('admin.divisions.edit', compact('division'));
    }

    /**
     * Update the specified division in storage.
     */
    public function update(UpdateDivisionRequest $request, Division $division)
    {
        $nama_divisi = strtolower(trim($request->validated('nama_divisi')));

        $division->update(['nama_divisi' => $nama_divisi]);

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Divisi berhasil diperbarui.');
    }

    /**
     * Remove the specified division from storage.
     */
    public function destroy(Division $division)
    {
        $division->delete();

        return redirect()->route('admin.divisions.index')->with('success', 'Divisi berhasil dihapus.');
    }
}
