<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Division extends Model
{
    use HasFactory;

    protected $fillable = ['nama_divisi'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('divisions_all');
        });

        static::deleted(function () {
            Cache::forget('divisions_all');
        });
    }
}
