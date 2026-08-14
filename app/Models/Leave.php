<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'pengganti_id',
        'kabag-pincab_id',
        'start_date',
        'end_date',
        'total_hari',
        'alasan',
        'proof_image',
        'status_pengganti',
        'status_kabag-pincab',
        'status_hrd',
        'status_final',
        'is_revision_pending',
        'revision_by_approval_id',
        'is_mendadak',
    ];

    protected $casts = [
        'is_revision_pending' => 'boolean',
        'is_mendadak'         => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }
    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class);
    }

    public function pengganti()
    {
        return $this->belongsTo(User::class, 'pengganti_id');
    }

    /** ambil riwayat terakhir tanpa N+1 saat rekap */
    public function lastHistory()
    {
        return $this->hasOne(ApprovalHistory::class)->latestOfMany();
    }

    public function revisionApproval()
    {
        return $this->belongsTo(Approval::class, 'revision_by_approval_id');
    }
}
