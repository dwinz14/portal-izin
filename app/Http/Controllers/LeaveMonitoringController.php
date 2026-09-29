<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use App\Models\Leave;
use App\Models\Office;
use App\Models\Position;
use App\Models\User;
use App\Models\UserLeaveBalance;
use App\Services\LeaveMonitoringService;
use Illuminate\Http\Request;

class LeaveMonitoringController extends Controller
{
    public function __construct(protected LeaveMonitoringService $monitoringService) {}

    /**
     * Daftar semua user (kecuali super_admin) beserta status izin/cuti
     * approved yang paling relevan, diurutkan yang sedang/akan izin duluan.
     */
    public function index(Request $request)
    {
        $search     = $request->get('search');
        $officeId   = $request->get('office_id');
        $positionId = $request->get('position_id');

        $users = $this->monitoringService->paginateUsers([
            'search'      => $search,
            'office_id'   => $officeId,
            'position_id' => $positionId,
        ]);

        $offices   = Office::orderBy('nama_kantor')->get();
        $positions = Position::orderBy('nama_jabatan')->get();

        return view('hrd.monitoring.index', compact(
            'users',
            'offices',
            'positions',
            'search',
            'officeId',
            'positionId'
        ));
    }

    /**
     * Detail 1 user: status izin/cuti terkini, kuota real-time semua jenis
     * cuti tahun berjalan, riwayat izin/cuti approved, riwayat kehadiran approved.
     */
    public function show(User $user)
    {
        $user->load(['office', 'position', 'division']);

        $status = $this->monitoringService->statusForUser($user);

        $balances = UserLeaveBalance::where('user_id', $user->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->whereHas('leaveType')
            ->get()
            ->sortBy(fn($balance) => $balance->leaveType->name ?? '');

        $leaves = Leave::where('user_id', $user->id)
            ->where('status_final', 'approved')
            ->with('leaveType')
            ->orderByDesc('start_date')
            ->paginate(10, ['*'], 'leaves_page');

        $attendances = AttendanceRequest::where('user_id', $user->id)
            ->where('status', AttendanceRequest::STATUS_APPROVED)
            ->orderByDesc('date')
            ->paginate(10, ['*'], 'attendance_page');

        return view('hrd.monitoring.show', compact(
            'user',
            'status',
            'balances',
            'leaves',
            'attendances'
        ));
    }
}
