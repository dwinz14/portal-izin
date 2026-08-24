<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    public const TYPE_LATE_ARRIVAL = 'late_arrival';
    public const TYPE_EARLY_DEPARTURE = 'early_departure';
    public const TYPE_LEAVE_DURING_WORK = 'leave_during_work';
    public const TYPE_UPDATE_ATTENDANCE = 'update_attendance';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TYPES = [
        self::TYPE_LATE_ARRIVAL,
        self::TYPE_EARLY_DEPARTURE,
        self::TYPE_LEAVE_DURING_WORK,
        self::TYPE_UPDATE_ATTENDANCE,
    ];

    protected $fillable = [
        'user_id',
        'approver_id',
        'type',
        'date',
        'start_time',
        'end_time',
        'reason',
        'proof_image',
        'status',
        'approved_at',
        'rejected_at',
        'approval_note',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public static function typeLabels(): array
    {
        return [
            self::TYPE_LATE_ARRIVAL => 'Datang Terlambat',
            self::TYPE_EARLY_DEPARTURE => 'Pulang Lebih Awal',
            self::TYPE_LEAVE_DURING_WORK => 'Meninggalkan Pekerjaan',
            self::TYPE_UPDATE_ATTENDANCE => 'Update Absensi',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }
}
