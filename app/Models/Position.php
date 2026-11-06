<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use App\Enums\WorkSchedule;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['nama_jabatan', 'work_schedule'];

    protected $casts = [
        'work_schedule' => WorkSchedule::class,
    ];

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
