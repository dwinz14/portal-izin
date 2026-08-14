<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Office extends Model
{
    use HasFactory;

    const PUSAT = 1;

    protected $fillable = ['nama_kantor'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('offices_all');
        });

        static::deleted(function () {
            Cache::forget('offices_all');
        });
    }
}
