<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\QuotaSetting;
use App\Models\LeaveType;
use App\Models\Office;
use App\Models\Position;
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

        // Get current settings
        $autoGenerate = QuotaSetting::getValue('auto_generate_leave_balances', true);
        $defaultQuota = QuotaSetting::getValue('default_annual_leave_quota', 12);

        return view('hrd.quota', compact(
            'userLeaveBalances',
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
            'defaultQuota'
        ));
    }

    public function resetAll(Request $request)
    {
        $leaveTypeId = $request->get('leave_type_id');
        $defaultQuota = $request->get('default_quota', 12);

        if ($leaveTypeId) {
            UserLeaveBalance::where('leave_type_id', $leaveTypeId)
                ->where('year', now()->year)
                ->update([
                    'total_quota' => $defaultQuota,
                    'remaining' => $defaultQuota,
                    'used' => 0
                ]);
        }

        return back()->with('success', 'Kuota cuti semua karyawan berhasil direset.');
    }

    public function resetDivision(Request $request)
    {
        $request->validate(['division_id' => 'required|exists:divisions,id']);
        $leaveTypeId = $request->get('leave_type_id');
        $defaultQuota = $request->get('default_quota', 12);

        if ($leaveTypeId) {
            UserLeaveBalance::whereHas('user', function ($query) use ($request) {
                $query->where('division_id', $request->division_id);
            })
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', now()->year)
                ->update([
                    'total_quota' => $defaultQuota,
                    'remaining' => $defaultQuota,
                    'used' => 0
                ]);
        }

        return back()->with('success', 'Kuota cuti divisi terpilih berhasil direset.');
    }

    public function resetPosition(Request $request)
    {
        $request->validate(['position_id' => 'required|exists:positions,id']);
        $leaveTypeId = $request->get('leave_type_id');
        $defaultQuota = $request->get('default_quota', 12);

        if ($leaveTypeId) {
            UserLeaveBalance::whereHas('user', function ($query) use ($request) {
                $query->where('position_id', $request->position_id);
            })
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', now()->year)
                ->update([
                    'total_quota' => $defaultQuota,
                    'remaining' => $defaultQuota,
                    'used' => 0
                ]);
        }

        return back()->with('success', 'Kuota cuti jabatan terpilih berhasil direset.');
    }

    public function update(Request $request, User $user, LeaveType $leaveType)
    {
        $request->validate([
            'remaining' => 'required|integer|min:0'
        ]);

        $balance = UserLeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->first();

        if ($balance) {
            $balance->update([
                'remaining' => $request->remaining,
                'total_quota' => $request->remaining + $balance->used
            ]);
        }

        return back()->with('success', "Kuota cuti {$user->name} berhasil diperbarui.");
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

        $year = $request->year ?: now()->year;

        DB::transaction(function () use ($year) {
            $users = User::where('role', '!=', 'super_admin')->get();
            $leaveTypes = LeaveType::where('is_active', true)->get();

            $created = 0;
            $skipped = 0;

            foreach ($users as $user) {
                foreach ($leaveTypes as $leaveType) {
                    // Skip jika jenis cuti khusus gender dan user tidak sesuai
                    if ($leaveType->gender && $leaveType->gender !== $user->gender) {
                        continue;
                    }

                    // Hitung masa kerja dalam tahun
                    $masaKerjaTahun = $user->masaKerjaTahun();

                    // Skip jika masa kerja kurang dari min_years
                    if ($masaKerjaTahun < $leaveType->min_years) {
                        continue;
                    }

                    // Gunakan updateOrCreate untuk menghindari duplikat
                    $balance = UserLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'leave_type_id' => $leaveType->id,
                            'year' => $year,
                        ],
                        [
                            'total_quota' => $leaveType->quota,
                            'remaining' => $leaveType->quota,
                            'used' => 0,
                        ]
                    );

                    if ($balance->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $skipped++;
                    }
                }
            }

            // Log aktivitas
            Log::info("Generate kuota cuti tahunan {$year}: {$created} dibuat, {$skipped} dilewati.");
        });

        return back()->with('success', "Generate kuota cuti tahunan untuk tahun {$year} berhasil. Silakan refresh halaman untuk melihat hasil.");
    }
}
