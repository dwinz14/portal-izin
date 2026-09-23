<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiExternal extends Model
{
    protected $connection = 'karyawan';

    protected $table = 'pegawai';

    protected $fillable = [];

    public $timestamps = false;

    /**
     * Hanya expose tiga field yang dibutuhkan.
     * Field lain di tabel pegawai tidak akan pernah terekspos.
     */
    protected $visible = [
        'pegawai_pin',
        'pegawai_nip',
        'pegawai_nama',
    ];

    /**
     * Autocomplete berdasarkan pegawai_nip (NIK) — untuk input saat register.
     * LIKE search dari awal string, maksimal 10 hasil.
     */
    public static function lookupByNik(string $keyword): \Illuminate\Support\Collection
    {
        return static::select('pegawai_pin', 'pegawai_nip', 'pegawai_nama')
            ->where('pegawai_nip', 'LIKE', $keyword . '%')
            ->limit(10)
            ->get();
    }

    /**
     * Validasi NIK saat submit register — exact match.
     */
    public static function findByNik(string $nik): ?self
    {
        return static::select('pegawai_pin', 'pegawai_nip', 'pegawai_nama')
            ->where('pegawai_nip', $nik)
            ->first();
    }
}
