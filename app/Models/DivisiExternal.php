<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DivisiExternal extends Model
{
    protected $connection = 'karyawan';
    protected $table      = 'pembagian2';
    protected $fillable   = [];
    public $timestamps    = false;

    protected $visible = [
        'pembagian2_id',
        'pembagian2_nama',
    ];

    public static function getAllNormalized(): Collection
    {
        return static::select('pembagian2_id', 'pembagian2_nama')
            ->whereNotNull('pembagian2_nama')
            ->where('pembagian2_nama', '!=', '')
            ->get()
            ->map(fn($d) => [
                'id'   => $d->pembagian2_id,
                'nama' => strtoupper(trim($d->pembagian2_nama)),
            ]);
    }

    /**
     * Ambil divisi dari DB karyawan yang belum ada di tabel divisions.
     */
    public static function getNewOnly(): Collection
    {
        $fromExternal = static::getAllNormalized()->pluck('nama');

        $existing = Division::pluck('nama_divisi')
            ->map(fn($n) => strtoupper(trim($n)))
            ->flip();

        return $fromExternal->filter(
            fn($nama) => ! $existing->has($nama)
        )->values();
    }
}
