<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PegawaiExternal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NikLookupController extends Controller
{
    /**
     * Autocomplete NIK dari DB karyawan.
     * Dipanggil via AJAX saat user mengetik NIK di form register.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $keyword = trim($request->input('q'));

        try {
            $results = PegawaiExternal::lookupByNik($keyword);

            if ($results->isEmpty()) {
                return response()->json([
                    'found'  => false,
                    'data'   => [],
                ]);
            }

            return response()->json([
                'found' => true,
                'data'  => $results->map(fn($p) => [
                    'nik'  => $p->pegawai_nip,
                    'pin'  => $p->pegawai_pin,
                    'nama' => $p->pegawai_nama,
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('NIK lookup failed: ' . $e->getMessage());

            return response()->json([
                'found'   => false,
                'data'    => [],
                'error'   => 'Layanan pencarian tidak tersedia sementara.',
            ], 503);
        }
    }

    /**
     * Validasi NIK exact match — dipanggil saat user memilih dari dropdown
     * untuk konfirmasi data sebelum form submit.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'nik' => ['required', 'string', 'max:50'],
        ]);

        $nik = trim($request->input('nik'));

        try {
            $pegawai = PegawaiExternal::findByNik($nik);

            if (! $pegawai) {
                return response()->json([
                    'valid' => false,
                    'data'  => null,
                ]);
            }

            // Cek apakah NIK sudah terdaftar di portal cuti
            $sudahTerdaftar = \App\Models\User::where('nik', $nik)->exists();

            return response()->json([
                'valid'           => true,
                'sudah_terdaftar' => $sudahTerdaftar,
                'data'            => [
                    'nik'  => $pegawai->pegawai_nip,
                    'pin'  => $pegawai->pegawai_pin,
                    'nama' => $pegawai->pegawai_nama,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('NIK validate failed: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'data'  => null,
                'error' => 'Layanan validasi tidak tersedia sementara.',
            ], 503);
        }
    }
}
