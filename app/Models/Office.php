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

    public static function groupedOfficeIds(?int $officeId): array
    {
        if (! $officeId) {
            return [];
        }

        $offices = Cache::remember('offices_all', 3600, fn() => static::all());

        $current = $offices->firstWhere('id', $officeId);

        if (! $current) {
            return [$officeId];
        }

        $currentName = strtolower(trim($current->nama_kantor));
        $groups = config('office_groups.pengganti_groups', []);

        foreach ($groups as $group) {
            $normalizedGroup = array_map(fn($name) => strtolower(trim($name)), $group);

            if (in_array($currentName, $normalizedGroup, true)) {
                return $offices
                    ->filter(fn($office) => in_array(strtolower(trim($office->nama_kantor)), $normalizedGroup, true))
                    ->pluck('id')
                    ->all();
            }
        }

        return [$officeId];
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
