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
        return static::select(
            'pegawai.pegawai_pin',
            'pegawai.pegawai_nip',
            'pegawai.pegawai_nama',
            'pembagian1.pembagian1_nama',
            'pembagian2.pembagian2_nama',
            'pembagian3.pembagian3_nama',
        )
            ->leftJoin('pembagian1', 'pegawai.pembagian1_id', '=', 'pembagian1.pembagian1_id')
            ->leftJoin('pembagian2', 'pegawai.pembagian2_id', '=', 'pembagian2.pembagian2_id')
            ->leftJoin('pembagian3', 'pegawai.pembagian3_id', '=', 'pembagian3.pembagian3_id')
            ->where('pegawai.pegawai_nip', $nik)
            ->first();
    }
}
