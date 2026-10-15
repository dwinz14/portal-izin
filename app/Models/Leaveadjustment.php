<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAdjustment extends Model
{
    public const TYPE_CANCELLED   = 'cancelled';
    public const TYPE_RESCHEDULED = 'rescheduled';

    protected $fillable = [
        'type',
        'leave_id',
        'actor_id',
        'employee_id',
        'alasan',
        'is_retroactive',
        'before',
        'after',
        'quota',
        'snapshot',
    ];

    protected $casts = [
        'is_retroactive' => 'boolean',
        'before'         => 'array',
        'after'          => 'array',
        'quota'          => 'array',
        'snapshot'       => 'array',
    ];

    /** Atasan yang melakukan perubahan. */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** Pemilik cuti. */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Cuti terkait. Bernilai null untuk pembatalan (baris cuti sudah dihapus;
     * rujuk kolom `before` / `snapshot` untuk detailnya).
     */
    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function isCancellation(): bool
    {
        return $this->type === self::TYPE_CANCELLED;
    }
}
