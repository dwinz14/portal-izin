<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class JabatanExternal extends Model
{
    protected $connection = 'karyawan';

    protected $table = 'pembagian1';

    protected $fillable = [];

    public $timestamps = false;

    protected $visible = [
        'pembagian1_nama',
    ];

    /**
     * Ambil semua jabatan dari DB karyawan,
     * sudah dinormalisasi ke UPPERCASE dan di-deduplicate.
     */
    public static function getAllNormalized(): Collection
    {
        return static::select('pembagian1_nama')
            ->whereNotNull('pembagian1_nama')
            ->where('pembagian1_nama', '!=', '')
            ->get()
            ->map(fn($j) => strtoupper(trim($j->pembagian1_nama)))
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Ambil jabatan dari DB karyawan yang belum ada di tabel positions.
     * Perbandingan dilakukan setelah normalisasi kedua sisi.
     */
    public static function getNewOnly(): Collection
    {
        $fromExternal = static::getAllNormalized();

        $existing = Position::pluck('nama_jabatan')
            ->map(fn($n) => strtoupper(trim($n)))
            ->flip(); // jadikan key untuk lookup O(1)

        return $fromExternal->filter(
            fn($nama) => ! $existing->has($nama)
        )->values();
    }
}
