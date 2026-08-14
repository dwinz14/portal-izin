<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Leave;

class DashboardController extends Controller
{

    public function index()
    {
        $userId = Auth::id();

        $leaveQuery = Leave::forUser($userId);

        $totalLeaveApplications = cache()->remember(
            "leave_count_user_{$userId}",
            now()->addMinutes(5),
            fn() => (clone $leaveQuery)->count()
        );

        $recentLeaves = (clone $leaveQuery)
            ->with(['leaveType:id,name', 'approvals.approver:id,name'])
            ->latest()
            ->limit(3)
            ->get();

        $pendingLeaves = (clone $leaveQuery)
            ->with(['leaveType:id,name', 'approvals.approver:id,name'])
            ->where('status_final', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalLeaveApplications',
            'recentLeaves',
            'pendingLeaves'
        ));
    }

    // public function admin()
    // {
    //     return view('admin.dashboard');
    // }

    // public function direksi()
    // {
    //     return view('direksi.dashboard');
    // }
    // public function hrd()
    // {
    //     return view('hrd.dashboard');
    // }
    // public function kabag()
    // {
    //     return view('kabag.dashboard');
    // }
    // public function kasie()
    // {
    //     return view('kasie.dashboard');
    // }
    // public function staff()
    // {
    //     return view('staff.dashboard');
    // }
}
