<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quota',
        'gender',
        'min_years',
        'advance_notice_days',
        'is_active',
        'is_annual_leave',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_annual_leave' => 'boolean',
        'advance_notice_days' => 'integer',
    ];

    public function userLeaveBalances()
    {
        return $this->hasMany(UserLeaveBalance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Apakah jenis cuti ini kategori izin sakit
     */
    public function isSickLeave(): bool
    {
        $name = strtolower((string) $this->name);

        return str_contains($name, 'izin sakit dengan surat dokter')
            || str_contains($name, 'izin sakit tanpa surat dokter');
    }

    /**
     * Apakah jenis cuti ini punya aturan batas pengajuan (aturan "mendadak").
     */
    public function hasAdvanceNoticeRule(): bool
    {
        return (int) $this->advance_notice_days > 0;
    }

    /**
     * Tanggal mulai paling awal (inklusif) agar pengajuan TIDAK tergolong mendadak.
     * null bila jenis ini tidak punya aturan mendadak.
     */
    public function earliestNormalStartDate(?Carbon $today = null): ?Carbon
    {
        if (! $this->hasAdvanceNoticeRule()) {
            return null;
        }

        return ($today ?? Carbon::today())->copy()->startOfDay()->addDays((int) $this->advance_notice_days);
    }

    /**
     * Apakah pengajuan dengan tanggal mulai ini tercatat sebagai MENDADAK.
     * Satu-satunya tempat aturan ini didefinisikan; backend memakainya saat menyimpan.
     */
    public function isMendadakFor(string|Carbon $startDate, ?Carbon $today = null): bool
    {
        $earliest = $this->earliestNormalStartDate($today);

        return $earliest !== null
            && Carbon::parse($startDate)->startOfDay()->lt($earliest);
    }
}
