<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class LeaveMonitoringService
{
    public const STATE_ONGOING   = 'ongoing';
    public const STATE_UPCOMING  = 'upcoming';
    public const STATE_COMPLETED = 'completed';
    public const STATE_NONE      = 'none';

    /**
     * @param array{search?: ?string, office_id?: ?int, position_id?: ?int} $filters
     */
    public function paginateUsers(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $today = Carbon::today()->toDateString();

        $ongoing = Leave::select('id')
            ->whereColumn('user_id', 'users.id')
            ->where('status_final', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('end_date')
            ->limit(1);

        $upcoming = Leave::select('id')
            ->whereColumn('user_id', 'users.id')
            ->where('status_final', 'approved')
            ->whereDate('start_date', '>', $today)
            ->orderBy('start_date')
            ->limit(1);

        $completed = Leave::select('id')
            ->whereColumn('user_id', 'users.id')
            ->where('status_final', 'approved')
            ->whereDate('end_date', '<', $today)
            ->orderByDesc('end_date')
            ->limit(1);

        $users = User::query()
            ->with(['office', 'position', 'division'])
            ->where('role', '!=', 'super_admin')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->when($filters['office_id'] ?? null, fn($q, $officeId) => $q->where('office_id', $officeId))
            ->when($filters['position_id'] ?? null, fn($q, $positionId) => $q->where('position_id', $positionId))
            ->addSelect([
                'ongoing_leave_id'   => $ongoing,
                'upcoming_leave_id'  => $upcoming,
                'completed_leave_id' => $completed,
            ])
            ->orderByRaw('
                CASE
                    WHEN ongoing_leave_id IS NOT NULL THEN 1
                    WHEN upcoming_leave_id IS NOT NULL THEN 2
                    WHEN completed_leave_id IS NOT NULL THEN 3
                    ELSE 4
                END
            ')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $this->attachLeaveInfo($users);

        return $users;
    }

    /**
     * Versi single-user dari logic status yang sama, dipakai di header
     * halaman detail (show). Tidak lewat batch subquery karena cuma 1 user.
     *
     * @return array{state: string, leave: ?Leave}
     */
    public function statusForUser(User $user): array
    {
        $today = Carbon::today()->toDateString();

        $ongoing = Leave::with('leaveType')
            ->where('user_id', $user->id)
            ->where('status_final', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('end_date')
            ->first();

        if ($ongoing) {
            return ['state' => self::STATE_ONGOING, 'leave' => $ongoing];
        }

        $upcoming = Leave::with('leaveType')
            ->where('user_id', $user->id)
            ->where('status_final', 'approved')
            ->whereDate('start_date', '>', $today)
            ->orderBy('start_date')
            ->first();

        if ($upcoming) {
            return ['state' => self::STATE_UPCOMING, 'leave' => $upcoming];
        }

        $completed = Leave::with('leaveType')
            ->where('user_id', $user->id)
            ->where('status_final', 'approved')
            ->whereDate('end_date', '<', $today)
            ->orderByDesc('end_date')
            ->first();

        if ($completed) {
            return ['state' => self::STATE_COMPLETED, 'leave' => $completed];
        }

        return ['state' => self::STATE_NONE, 'leave' => null];
    }

    /**
     * Tempelkan atribut computed `monitoring_state` & `monitoring_leave` ke
     * tiap user di halaman ini, lewat 1 query batch (bukan query per-user).
     */
    protected function attachLeaveInfo(LengthAwarePaginator $users): void
    {
        $leaveIds = $users->getCollection()
            ->flatMap(fn(User $u) => [$u->ongoing_leave_id, $u->upcoming_leave_id, $u->completed_leave_id])
            ->filter()
            ->unique()
            ->values();

        $leaves = $leaveIds->isEmpty()
            ? collect()
            : Leave::with('leaveType')->whereIn('id', $leaveIds)->get()->keyBy('id');

        $users->getCollection()->transform(function (User $user) use ($leaves) {
            if ($user->ongoing_leave_id && $leaves->has($user->ongoing_leave_id)) {
                $user->monitoring_state = self::STATE_ONGOING;
                $user->monitoring_leave = $leaves->get($user->ongoing_leave_id);
            } elseif ($user->upcoming_leave_id && $leaves->has($user->upcoming_leave_id)) {
                $user->monitoring_state = self::STATE_UPCOMING;
                $user->monitoring_leave = $leaves->get($user->upcoming_leave_id);
            } elseif ($user->completed_leave_id && $leaves->has($user->completed_leave_id)) {
                $user->monitoring_state = self::STATE_COMPLETED;
                $user->monitoring_leave = $leaves->get($user->completed_leave_id);
            } else {
                $user->monitoring_state = self::STATE_NONE;
                $user->monitoring_leave = null;
            }

            return $user;
        });
    }
}
