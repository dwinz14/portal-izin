<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class KantorExternal extends Model
{
    protected $connection = 'karyawan';
    protected $table      = 'pembagian3';
    protected $fillable   = [];
    public $timestamps    = false;

    protected $visible = [
        'pembagian3_id',
        'pembagian3_nama',
    ];

    public static function getAllNormalized(): Collection
    {
        return static::select('pembagian3_id', 'pembagian3_nama')
            ->whereNotNull('pembagian3_nama')
            ->where('pembagian3_nama', '!=', '')
            ->get()
            ->map(fn($k) => [
                'id'   => $k->pembagian3_id,
                'nama' => strtoupper(trim($k->pembagian3_nama)),
            ]);
    }

    /**
     * Ambil kantor dari DB karyawan yang belum ada di tabel offices.
     */
    public static function getNewOnly(): Collection
    {
        $fromExternal = static::getAllNormalized()->pluck('nama');

        $existing = Office::pluck('nama_kantor')
            ->map(fn($n) => strtoupper(trim($n)))
            ->flip();

        return $fromExternal->filter(
            fn($nama) => ! $existing->has($nama)
        )->values();
    }
}
