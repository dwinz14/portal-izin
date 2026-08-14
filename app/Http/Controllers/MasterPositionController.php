<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class MasterPositionController extends Controller
{

    /**
     * Display a listing of the positions.
     */
    public function index(Request $request)
    {

        $positions = Position::query()
            ->paginate(10);

        return view('admin.positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create()
    {
        return view('admin.positions.create');
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:positions,nama_jabatan',
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_jabatan = strtolower(trim($request->nama_jabatan));

        Position::create([
            'nama_jabatan' => $nama_jabatan,
        ]);

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position)
    {
        return view('admin.positions.edit', compact('position'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(Request $request, Position $position)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/|unique:positions,nama_jabatan,' . $position->id,
        ]);

        // Sanitize and normalize: trim and convert to lowercase
        $nama_jabatan = strtolower(trim($request->nama_jabatan));

        $position->update([
            'nama_jabatan' => $nama_jabatan,
        ]);

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('admin.positions.index')->with('success', 'Jabatan berhasil dihapus.');
    }
}
