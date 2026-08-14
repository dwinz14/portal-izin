<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\LeaveType;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nik',
        'name',
        'email',
        'password',
        'role',
        'status',
        'division_id',
        'position_id',
        'office_id',
        'tanggal_aktif_kerja',
        // 'sisa_cuti',
        'last_login_at',
        'must_change_password',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function userLeaveBalances()
    {
        return $this->hasMany(UserLeaveBalance::class);
    }

    public function masaKerjaTahun()
    {
        if (!$this->tanggal_aktif_kerja) return 0;

        return Carbon::parse($this->tanggal_aktif_kerja)->diffInYears(now());
    }

    public function masaKerjaTahunBulan()
    {
        if (!$this->tanggal_aktif_kerja) return 'Belum ditentukan';

        $startDate = Carbon::parse($this->tanggal_aktif_kerja);
        $now = now();

        $years = (int) $startDate->diffInYears($now);
        $months = (int) $startDate->diffInMonths($now) % 12;

        if ($years == 0 && $months == 0) {
            return 'Kurang dari 1 bulan';
        }

        $result = [];
        if ($years > 0) {
            $result[] = $years . ' tahun';
        }
        if ($months > 0) {
            $result[] = $months . ' bulan';
        }

        return implode(' ', $result);
    }

    public function eligibleForAnnualLeave()
    {
        $cutiTahunan = LeaveType::where('name', 'cuti tahunan')->first();
        if (!$cutiTahunan) return false;

        return $this->masaKerjaTahun() >= $cutiTahunan->min_years;
    }

    /**
     * Get annual leave balance for current year
     */
    public function getAnnualLeaveBalance()
    {
        $annualLeaveType = \App\Models\LeaveType::where('name', 'Cuti Tahunan')->first();
        if (!$annualLeaveType) {
            return 0;
        }

        $balance = $this->userLeaveBalances()
            ->where('leave_type_id', $annualLeaveType->id)
            ->where('year', now()->year)
            ->first();

        return $balance ? $balance->remaining : 0;
    }

    /**
     * Create leave balances for a specific year
     */
    public function createLeaveBalancesForYear($year)
    {
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();

        foreach ($leaveTypes as $leaveType) {
            // Skip if gender-specific and user doesn't match
            if ($leaveType->gender && $leaveType->gender !== $this->gender) {
                continue;
            }

            // Check if balance already exists
            $existing = $this->userLeaveBalances()
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->exists();

            if (!$existing) {
                $this->userLeaveBalances()->create([
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                    'total_quota' => $leaveType->quota,
                    'remaining' => $leaveType->quota,
                ]);
            }
        }
    }

    /**
     * Get the username field for authentication.
     */
    public function username()
    {
        return 'nik';
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Auto-create leave balances if setting is enabled
            if (\App\Models\QuotaSetting::getValue('auto_generate_leave_balances', true)) {
                $user->createLeaveBalancesForYear(now()->year);
            }
        });
    }
}
