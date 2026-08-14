<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class MasterOfficeController extends Controller
{

    /**
     * Display a listing of the offices.
     */
    public function index(Request $request)
    {

        $offices = Office::query()
            ->paginate(10);

        return view('admin.offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new office.
     */
    public function create()
    {
        return view('admin.offices.create');
    }

    /**
     * Store a newly created office in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kantor' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:offices,nama_kantor',
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_kantor = strtolower(trim($request->nama_kantor));

        Office::create([
            'nama_kantor' => $nama_kantor,
        ]);

        return redirect()->route('admin.offices.index')->with('success', 'Kantor berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified office.
     */
    public function edit(Office $office)
    {
        return view('admin.offices.edit', compact('office'));
    }

    /**
     * Update the specified office in storage.
     */
    public function update(Request $request, Office $office)
    {
        $request->validate([
            'nama_kantor' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:offices,nama_kantor,' . $office->id,
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_kantor = strtolower(trim($request->nama_kantor));

        $office->update([
            'nama_kantor' => $nama_kantor,
        ]);

        return redirect()->route('admin.offices.index')->with('success', 'Kantor berhasil diperbarui.');
    }

    /**
     * Remove the specified office from storage.
     */
    public function destroy(Office $office)
    {
        $office->delete();

        return redirect()->route('admin.offices.index')->with('success', 'Kantor berhasil dihapus.');
    }
}
