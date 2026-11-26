<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_id',
        'approver_id',
        'step',
        'status',
        'revised_start_date',
        'revised_end_date',
        'revised_total_hari',
        'revised_at'
    ];

    protected $casts = [
        'revised_start_date' => 'date',
        'revised_end_date' => 'date',
        'revised_at' => 'datetime',
    ];


    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    public function scopePendingForActiveOwner(Builder $query, int $approverId): Builder
    {
        return $query->where('approver_id', $approverId)
            ->where('status', 'pending')
            ->whereHas('leave.user');
    }
    public function isFinalStep(): bool
    {
        return (int) $this->step === (int) $this->leave->approvals->max('step');
    }
}
