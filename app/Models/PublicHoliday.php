<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PublicHoliday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'type',
        'year',
        'description',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Pastikan kolom `year` selalu sinkron dengan `date`, supaya
     * tidak mungkin admin input year yang berbeda dari tanggalnya.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (PublicHoliday $holiday) {
            if ($holiday->date) {
                $holiday->year = Carbon::parse($holiday->date)->year;
            }
        });
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year)->where('is_active', true);
    }

    public function scopeNationalHolidays($query)
    {
        return $query->where('type', 'national_holiday');
    }

    public function scopeJointLeaves($query)
    {
        return $query->where('type', 'joint_leave');
    }

    // ── Static helpers (dengan cache 24 jam) ───────────────────────

    /**
     * Daftar tanggal libur nasional (tanggal merah) aktif di tahun tertentu.
     * Hanya libur nasional — cuti bersama dipisah karena tetap dihitung
     * sebagai hari cuti (memakai kuota tahunan).
     */
    public static function getHolidayDatesForYear(int $year): array
    {
        return Cache::remember("public_holidays_{$year}", 86400, function () use ($year) {
            return static::forYear($year)
                ->nationalHolidays()
                ->pluck('date')
                ->map(fn ($d) => $d->format('Y-m-d'))
                ->toArray();
        });
    }

    public static function getJointLeaveDatesForYear(int $year): array
    {
        return Cache::remember("joint_leave_{$year}", 86400, function () use ($year) {
            return static::forYear($year)
                ->jointLeaves()
                ->pluck('date')
                ->map(fn ($d) => $d->format('Y-m-d'))
                ->toArray();
        });
    }

    /**
     * Map seluruh hari libur aktif per tanggal, untuk keperluan view
     * (menandai tanggal merah di kalender form cuti).
     *
     * Format: ['Y-m-d' => ['name' => ..., 'type' => 'national_holiday|joint_leave']]
     */
    public static function getHolidayMap(): array
    {
        return Cache::remember('public_holidays_map', 86400, function () {
            return static::where('is_active', true)
                ->get()
                ->mapWithKeys(fn ($h) => [
                    $h->date->format('Y-m-d') => [
                        'name' => $h->name,
                        'type' => $h->type,
                    ],
                ])
                ->all();
        });
    }

    /**
     * Hanya menghitung cuti bersama yang jatuh di hari kerja
     * (Senin-Jumat) — yang jatuh Sabtu/Minggu tidak mengurangi
     * kuota karena tidak ada hari kerja yang sebenarnya hilang.
     */
    public static function countJointLeaveForYear(int $year): int
    {
        return Cache::remember("joint_leave_count_{$year}", 86400, function () use ($year) {
            return static::forYear($year)
                ->jointLeaves()
                ->get()
                ->filter(fn ($h) => $h->date->isWeekday())
                ->count();
        });
    }

    public static function clearCacheForYear(int $year): void
    {
        Cache::forget("public_holidays_{$year}");
        Cache::forget("joint_leave_{$year}");
        Cache::forget("joint_leave_count_{$year}");
        Cache::forget('public_holidays_map');
    }
}
