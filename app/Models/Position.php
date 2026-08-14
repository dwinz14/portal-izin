<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['nama_jabatan'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('positions_all');
        });

        static::deleted(function () {
            Cache::forget('positions_all');
        });
    }
}
