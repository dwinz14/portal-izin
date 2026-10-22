<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

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
        'quota_year',
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
        'quota_year'          => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Cuti approved yang BOLEH dikelola (batalkan / ubah tanggal / ubah pengganti) oleh $user atasan.
     */
    public function scopeAdjustableBy(Builder $query, User $user): Builder
    {
        return $query->where('status_final', 'approved')
            ->where(function (Builder $q) use ($user) {
                $q->whereHas('approvals', function ($a) use ($user) {
                    $a->where('approver_id', $user->id)
                        ->where('status', 'approved')
                        ->where('step', function ($sub) {
                            $sub->from('approvals as latest')
                                ->selectRaw('MAX(latest.step)')
                                ->whereColumn('latest.leave_id', 'approvals.leave_id');
                        });
                });

                $role = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;

                if ($role === 'hrd') {
                    $q->orWhere(function (Builder $direksi) {
                        $direksi->whereDoesntHave('approvals')
                            ->whereHas('user', fn($u) => $u->where('role', 'direksi'));
                    });
                }
            });
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

    public function finalApproval(): ?Approval
    {
        return $this->approvals->sortByDesc('step')->first();
    }

    /** Atasan = approver pada langkah terakhir. */
    public function atasan(): ?User
    {
        return $this->finalApproval()?->approver;
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

    public function penggantiChanges()
    {
        return $this->hasMany(LeavePenggantiChange::class)->latest();
    }

    protected static function booted(): void
    {
        static::deleted(function (self $model) {
            app(\App\Services\ImageCompressionService::class)->delete($model->proof_image);
        });
    }
}
