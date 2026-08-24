<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use App\Notifications\AttendanceRequestApproved;
use App\Notifications\AttendanceRequestRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceApprovalController extends Controller
{
    public function index()
    {
        $attendanceRequests = AttendanceRequest::with(['user.position', 'user.office'])
            ->where('approver_id', Auth::id())
            ->where('status', AttendanceRequest::STATUS_PENDING)
            ->oldest()
            ->paginate(10);

        return view('attendance-approvals.index', compact('attendanceRequests'));
    }

    public function approve(AttendanceRequest $attendanceRequest, Request $request)
    {
        $this->authorizeAttendanceApproval($attendanceRequest);

        $request->validate([
            'approval_note' => ['nullable', 'string', 'max:500'],
        ]);

        $attendanceRequest->update([
            'status' => AttendanceRequest::STATUS_APPROVED,
            'approved_at' => now(),
            'approval_note' => $request->approval_note,
        ]);

        $attendanceRequest->user?->notify(new AttendanceRequestApproved($attendanceRequest, Auth::user()->name));

        return back()->with('success', 'Pengajuan kehadiran disetujui.');
    }

    public function reject(AttendanceRequest $attendanceRequest, Request $request)
    {
        $this->authorizeAttendanceApproval($attendanceRequest);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $attendanceRequest->update([
            'status' => AttendanceRequest::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        $attendanceRequest->user?->notify(new AttendanceRequestRejected($attendanceRequest, Auth::user()->name));

        return back()->with('success', 'Pengajuan kehadiran ditolak.');
    }

    private function authorizeAttendanceApproval(AttendanceRequest $attendanceRequest): void
    {
        abort_unless($attendanceRequest->approver_id === Auth::id(), 403);
        abort_if($attendanceRequest->status !== AttendanceRequest::STATUS_PENDING, 400);
    }
}
