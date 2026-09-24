<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\KantorExternal;
use Illuminate\Support\Facades\Log;
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

    public function sync()
    {
        try {
            $newKantor = KantorExternal::getNewOnly();
        } catch (\Exception $e) {
            Log::error('Sync kantor gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.offices.index')
                ->with('error', 'Koneksi ke database karyawan gagal. Coba beberapa saat lagi.');
        }

        $offices = Office::query()->paginate(10);

        return view('admin.offices.index', compact('offices', 'newKantor'));
    }

    public function insertFromExternal(Request $request)
    {
        $request->validate([
            'nama_kantor' => ['required', 'string', 'max:255'],
        ]);

        $nama = strtoupper(trim($request->nama_kantor));

        $sudahAda = Office::whereRaw('UPPER(TRIM(nama_kantor)) = ?', [$nama])->exists();

        if ($sudahAda) {
            return redirect()
                ->route('admin.offices.sync')
                ->with('error', "Kantor \"{$nama}\" sudah ada di daftar kantor.");
        }

        try {
            $valid = KantorExternal::getAllNormalized()
                ->pluck('nama')
                ->contains($nama);

            if (! $valid) {
                return redirect()
                    ->route('admin.offices.sync')
                    ->with('error', 'Kantor tidak ditemukan di database karyawan.');
            }
        } catch (\Exception $e) {
            Log::error('Insert kantor external gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.offices.sync')
                ->with('error', 'Koneksi ke database karyawan gagal saat verifikasi.');
        }

        // Simpan lowercase — konsisten dengan store() yang sudah ada
        Office::create(['nama_kantor' => strtolower($nama)]);

        return redirect()
            ->route('admin.offices.sync')
            ->with('success', "Kantor \"{$nama}\" berhasil ditambahkan.");
    }

    public function insertAllFromExternal()
    {
        try {
            $newKantor = KantorExternal::getNewOnly();
        } catch (\Exception $e) {
            Log::error('Insert all kantor external gagal: ' . $e->getMessage());
            return redirect()
                ->route('admin.offices.sync')
                ->with('error', 'Koneksi ke database karyawan gagal.');
        }

        if ($newKantor->isEmpty()) {
            return redirect()
                ->route('admin.offices.sync')
                ->with('info', 'Tidak ada kantor baru yang perlu ditambahkan.');
        }

        $inserted = 0;
        foreach ($newKantor as $nama) {
            $sudahAda = Office::whereRaw('UPPER(TRIM(nama_kantor)) = ?', [$nama])->exists();
            if (! $sudahAda) {
                Office::create(['nama_kantor' => strtolower($nama)]);
                $inserted++;
            }
        }

        return redirect()
            ->route('admin.offices.sync')
            ->with('success', "{$inserted} kantor baru berhasil ditambahkan.");
    }
}
