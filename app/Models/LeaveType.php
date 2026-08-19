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
}
