<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\DivisiExternal;
use Illuminate\Support\Facades\Log;
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

    public function sync()
    {
        try {
            $newDivisi = DivisiExternal::getNewOnly();
        } catch (\Exception $e) {
            Log::error('Sync divisi gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.divisions.index')
                ->with('error', 'Koneksi ke database karyawan gagal. Coba beberapa saat lagi.');
        }

        $divisions = Division::query()->paginate(10);

        return view('admin.divisions.index', compact('divisions', 'newDivisi'));
    }

    public function insertFromExternal(Request $request)
    {
        $request->validate([
            'nama_divisi' => ['required', 'string', 'max:255'],
        ]);

        $nama = strtoupper(trim($request->nama_divisi));

        $sudahAda = Division::whereRaw('UPPER(TRIM(nama_divisi)) = ?', [$nama])->exists();

        if ($sudahAda) {
            return redirect()
                ->route('admin.divisions.sync')
                ->with('error', "Divisi \"{$nama}\" sudah ada di daftar divisi.");
        }

        try {
            $valid = DivisiExternal::getAllNormalized()
                ->pluck('nama')
                ->contains($nama);

            if (! $valid) {
                return redirect()
                    ->route('admin.divisions.sync')
                    ->with('error', 'Divisi tidak ditemukan di database karyawan.');
            }
        } catch (\Exception $e) {
            Log::error('Insert divisi external gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.divisions.sync')
                ->with('error', 'Koneksi ke database karyawan gagal saat verifikasi.');
        }

        // Simpan lowercase — konsisten dengan store() yang sudah ada
        Division::create(['nama_divisi' => strtolower($nama)]);

        return redirect()
            ->route('admin.divisions.sync')
            ->with('success', "Divisi \"{$nama}\" berhasil ditambahkan.");
    }

    public function insertAllFromExternal()
    {
        try {
            $newDivisi = DivisiExternal::getNewOnly();
        } catch (\Exception $e) {
            Log::error('Insert all divisi external gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.divisions.sync')
                ->with('error', 'Koneksi ke database karyawan gagal.');
        }

        if ($newDivisi->isEmpty()) {
            return redirect()
                ->route('admin.divisions.sync')
                ->with('info', 'Tidak ada divisi baru yang perlu ditambahkan.');
        }

        $inserted = 0;
        foreach ($newDivisi as $nama) {
            $sudahAda = Division::whereRaw('UPPER(TRIM(nama_divisi)) = ?', [$nama])->exists();
            if (! $sudahAda) {
                Division::create(['nama_divisi' => strtolower($nama)]);
                $inserted++;
            }
        }

        return redirect()
            ->route('admin.divisions.sync')
            ->with('success', "{$inserted} divisi baru berhasil ditambahkan.");
    }
}
