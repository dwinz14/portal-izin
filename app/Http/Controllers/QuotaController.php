<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Office;
use App\Models\Position;
use App\Models\PublicHoliday;
use App\Models\QuotaSetting;
use App\Models\User;
use App\Models\UserLeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotaController extends Controller
{
    public function index(Request $request)
    {
        $positions = Position::all();
        $offices = Office::all();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        // Get selected leave type (default to first active one)
        $leaveTypeId = $request->get('leave_type_id', $leaveTypes->first()?->id);
        $selectedLeaveType = LeaveType::find($leaveTypeId);

        $search = $request->get('search');
        $positionId = $request->get('position_id');
        $officeId = $request->get('office_id');
        $role = $request->get('role');

        // Get user leave balances for selected leave type
        $userLeaveBalances = UserLeaveBalance::with(['user.division', 'leaveType'])
            ->join('users', 'user_leave_balances.user_id', '=', 'users.id')
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', now()->year)
            ->where('users.role', '!=', 'super_admin')
            ->when($search, function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%");
            })
            ->when($positionId, function ($q) use ($positionId) {
                $q->where('users.position_id', $positionId);
            })
            ->when($officeId, function ($q) use ($officeId) {
                $q->where('users.office_id', $officeId);
            })
            ->when($role, function ($q) use ($role) {
                $q->where('users.role', $role);
            })
            ->orderBy('users.name')
            ->select('user_leave_balances.*')
            ->paginate(8)
            ->withQueryString();

        // Get ALL user IDs + names (no pagination) matching same filters — for "select all" feature
        $allUserBalances = UserLeaveBalance::with('user:id,name')
            ->join('users', 'user_leave_balances.user_id', '=', 'users.id')
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', now()->year)
            ->where('users.role', '!=', 'super_admin')
            ->when($search, function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%");
            })
            ->when($positionId, function ($q) use ($positionId) {
                $q->where('users.position_id', $positionId);
            })
            ->when($officeId, function ($q) use ($officeId) {
                $q->where('users.office_id', $officeId);
            })
            ->when($role, function ($q) use ($role) {
                $q->where('users.role', $role);
            })
            ->orderBy('users.name')
            ->select('user_leave_balances.user_id', 'users.name')
            ->get();

        // Get current settings
        $autoGenerate = QuotaSetting::getValue('auto_generate_leave_balances', true);
        $defaultQuota = QuotaSetting::getValue('default_annual_leave_quota', 12);

        // Info kuota cuti tahunan: 12 - jumlah cuti bersama tahun berjalan
        $jointLeaveCount = PublicHoliday::countJointLeaveForYear(now()->year);
        $effectiveQuota = max(0, $defaultQuota - $jointLeaveCount);

        return view('hrd.quota', compact(
            'userLeaveBalances',
            'allUserBalances',
            'leaveTypes',
            'leaveTypeId',
            'selectedLeaveType',
            'positions',
            'positionId',
            'offices',
            'officeId',
            'search',
            'role',
            'autoGenerate',
            'defaultQuota',
            'jointLeaveCount',
            'effectiveQuota'
        ));
    }

    public function resetAll(Request $request)
    {
        $leaveTypeId  = $request->get('leave_type_id');
        $defaultQuota = (int) $request->get('default_quota', 12);

        if ($leaveTypeId) {
            UserLeaveBalance::where('leave_type_id', $leaveTypeId)
                ->where('year', now()->year)
                ->update([
                    'total_quota' => $defaultQuota,
                    'remaining'   => \Illuminate\Support\Facades\DB::raw(
                        "GREATEST(0, {$defaultQuota} - used)"
                    ),
                ]);
        }

        return back()->with('success', 'Kuota cuti semua karyawan berhasil diperbarui.');
    }

    public function resetDivision(Request $request)
    {
        $request->validate(['division_id' => 'required|exists:divisions,id']);
        $leaveTypeId  = $request->get('leave_type_id');
        $defaultQuota = (int) $request->get('default_quota', 12);

        if ($leaveTypeId) {
            UserLeaveBalance::whereHas('user', function ($query) use ($request) {
                $query->where('division_id', $request->division_id);
            })
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', now()->year)
                ->update([
                    'total_quota' => $defaultQuota,
                    'remaining'   => \Illuminate\Support\Facades\DB::raw(
                        "GREATEST(0, {$defaultQuota} - used)"
                    ),
                ]);
        }

        return back()->with('success', 'Kuota cuti divisi terpilih berhasil diperbarui.');
    }

    public function resetPosition(Request $request)
    {
        $request->validate(['position_id' => 'required|exists:positions,id']);
        $leaveTypeId  = $request->get('leave_type_id');
        $defaultQuota = (int) $request->get('default_quota', 12);

        if ($leaveTypeId) {
            UserLeaveBalance::whereHas('user', function ($query) use ($request) {
                $query->where('position_id', $request->position_id);
            })
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', now()->year)
                ->update([
                    'total_quota' => $defaultQuota,
                    'remaining'   => \Illuminate\Support\Facades\DB::raw(
                        "GREATEST(0, {$defaultQuota} - used)"
                    ),
                ]);
        }

        return back()->with('success', 'Kuota cuti jabatan terpilih berhasil diperbarui.');
    }

    public function update(Request $request, User $user, LeaveType $leaveType)
    {
        $request->validate([
            'remaining' => 'required|integer|min:0',
        ]);

        $balance = UserLeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->first();

        if ($balance) {
            $balance->update([
                'remaining' => $request->remaining,
                'total_quota' => $request->remaining + $balance->used,
            ]);
        }

        return back()->with('success', "Kuota cuti {$user->name} berhasil diperbarui.");
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'leave_type_id'  => 'required|exists:leave_types,id',
            'mode'           => 'required|in:set,add,subtract',
            'value'          => 'required|integer|min:0',
            'user_ids'       => 'required|array|min:1',
            'user_ids.*'     => 'integer|exists:users,id',
        ]);

        $leaveTypeId = $request->leave_type_id;
        $mode        = $request->mode;
        $value       = (int) $request->value;
        $userIds     = $request->user_ids;
        $year        = now()->year;

        $updated = 0;

        DB::transaction(function () use ($leaveTypeId, $mode, $value, $userIds, $year, &$updated) {
            $balances = UserLeaveBalance::where('leave_type_id', $leaveTypeId)
                ->where('year', $year)
                ->whereIn('user_id', $userIds)
                ->get();

            foreach ($balances as $balance) {
                $newRemaining = match ($mode) {
                    'set'      => $value,
                    'add'      => $balance->remaining + $value,
                    'subtract' => max(0, $balance->remaining - $value),
                };

                $balance->update([
                    'remaining'   => $newRemaining,
                    'total_quota' => $newRemaining + $balance->used,
                ]);

                $updated++;
            }
        });

        $modeLabel = match ($mode) {
            'set'      => "diset ke {$value} hari",
            'add'      => "ditambah {$value} hari",
            'subtract' => "dikurangi {$value} hari",
        };

        Log::info("Bulk update kuota: {$updated} karyawan {$modeLabel} (leave_type_id={$leaveTypeId}, tahun={$year})");

        return back()->with('success', "Kuota {$updated} karyawan berhasil {$modeLabel}.");
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'auto_generate_leave_balances' => 'nullable|boolean',
            'default_annual_leave_quota' => 'nullable|integer|min:0',
        ]);

        // Update auto-generate setting
        if ($request->has('auto_generate_leave_balances')) {
            QuotaSetting::setValue(
                'auto_generate_leave_balances',
                $request->boolean('auto_generate_leave_balances'),
                'boolean',
                'Otomatis buat saldo cuti untuk user baru'
            );
        }

        // Update default quota setting
        if ($request->has('default_annual_leave_quota')) {
            QuotaSetting::setValue(
                'default_annual_leave_quota',
                $request->integer('default_annual_leave_quota'),
                'integer',
                'Kuota cuti tahunan default'
            );
        }

        return back()->with('success', 'Pengaturan kuota berhasil diperbarui.');
    }

    public function generateAnnualBalances(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
        ]);

        $year   = $request->year ?: now()->year;
        $result = app(\App\Services\LeaveQuotaService::class)->generateForAllUsers($year);

        $jointLeaveCount = \App\Models\PublicHoliday::countJointLeaveForYear($year);

        $message = "Generate kuota cuti tahun {$year} selesai: "
            . "{$result['created']} dibuat, {$result['updated']} diperbarui, "
            . "{$result['skipped']} dilewati.";

        if ($jointLeaveCount > 0) {
            $message .= " Kuota cuti tahunan dikurangi {$jointLeaveCount} hari cuti bersama.";
        }

        return back()->with('success', $message);
    }
}
