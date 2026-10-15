<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quota',
        'gender',
        'min_years',
        'is_active',
        'is_annual_leave',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_annual_leave' => 'boolean',
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
}
