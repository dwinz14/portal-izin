<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quota',
        'gender',
        'min_years',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
