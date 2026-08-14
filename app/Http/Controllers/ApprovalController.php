<?php

namespace App\Http\Controllers;

use App\Helpers\LeaveHelper;
use App\Models\Approval;
use App\Notifications\LeaveRequestApproved;
use App\Notifications\LeaveRequestSubmitted;
use App\Notifications\LeaveFinalApproved;
use App\Models\ApprovalHistory;
use App\Models\User;
use App\Notifications\LeaveRequestRejected;
use App\Notifications\LeaveRequestRevisionRequested;
use App\Services\LeaveApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Subquery untuk mengecek apakah semua approval step sebelumnya sudah approved
        $approvals = Approval::with(['leave.user', 'approver'])
            ->where('approver_id', $userId)
            ->where('status', 'pending')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('approvals as a2')
                    ->whereColumn('a2.leave_id', 'approvals.leave_id')
                    ->whereColumn('a2.step', '<', 'approvals.step')
                    ->where('a2.status', '!=', 'approved');
            })
            ->get();

        return view('approvals.index', compact('approvals'));
    }

    public function history()
    {
        $histories = ApprovalHistory::with([
            'leave.user.position',
            'leave.leaveType',
            'leave.approvals' => function ($query) {
                $query->orderBy('step');
            },
            'approver'
        ])
            ->where('approved_by', Auth::id())
            ->latest()
            ->paginate(10);

        return view('approvals.history', compact('histories'));
    }

    public function approve(Approval $approval, Request $request)
    {
        $this->authorizeApproval($approval);

        $approval->update(['status' => 'approved']);

        ApprovalHistory::create([
            'leave_id'    => $approval->leave_id,
            'approved_by' => Auth::id(),
            'role'        => Auth::user()->role,
            'step'        => $approval->step,
            'status'      => 'approved',
            'catatan'     => $request->input('catatan'),
        ]);

        $leave = $approval->leave;
        $employee = $leave->user;

        // Beritahu pemohon bahwa step ini sudah disetujui
        $employee->notify(new LeaveRequestApproved($leave->id, Auth::user()->name));

        // Cek apakah masih ada step approval berikutnya
        $nextApproval = $leave->approvals()
            ->where('step', '>', $approval->step)
            ->orderBy('step')
            ->first();

        if ($nextApproval) {
            // Masih ada approver berikutnya, kirim notifikasi ke mereka
            $nextApprover = User::find($nextApproval->approver_id);
            if ($nextApprover) {
                $nextApprover->notify(new LeaveRequestSubmitted($leave->id, $leave->user->name));
            }
        } else {
            // Semua step selesai, lakukan final approval
            $this->finalApprove($leave);
        }

        return back()->with('success', 'Approval disetujui.');
    }

    public function requestRevision(Approval $approval, Request $request)
    {
        $this->authorizeApproval($approval);

        $request->validate([
            'revised_start_date' => 'required|date',
            'revised_end_date' => 'required|date|after_or_equal:revised_start_date',
        ]);

        $revisedTotalHari = LeaveHelper::calculateWorkingDays(
            $request->revised_start_date,
            $request->revised_end_date
        );

        // Update approval dengan data revisi
        $approval->update([
            'revised_start_date' => $request->revised_start_date,
            'revised_end_date' => $request->revised_end_date,
            'revised_total_hari' => $revisedTotalHari,
            'revised_at' => now(),
        ]);

        // Update leave status
        $approval->leave->update([
            'is_revision_pending' => true,
            'revision_by_approval_id' => $approval->id,
        ]);

        ApprovalHistory::create([
            'leave_id'    => $approval->leave_id,
            'approved_by' => Auth::id(),
            'role'        => Auth::user()->role,
            'step'        => $approval->step,
            'status'      => 'revision_requested',
            'catatan'     => "Revisi tanggal: {$request->revised_start_date} s/d {$request->revised_end_date} ({$revisedTotalHari} hari)",
        ]);

        // Kirim notifikasi ke pemohon
        $approval->leave->user->notify(new LeaveRequestRevisionRequested($approval->leave_id, Auth::user()->name));

        return back()->with('success', 'Permintaan revisi tanggal telah dikirim ke pemohon.');
    }

    public function reject(Approval $approval, Request $request)
    {
        $this->authorizeApproval($approval);

        $approval->update(['status' => 'rejected']);

        $approval->leave->update(['status_final' => 'rejected']);

        ApprovalHistory::create([
            'leave_id'    => $approval->leave_id,
            'approved_by' => Auth::id(),
            'role'        => Auth::user()->role,
            'step'        => $approval->step,
            'status'      => 'rejected',
            'catatan'     => $request->input('catatan'),
        ]);

        // Kirim notifikasi ke pemohon cuti
        $approval->leave->user->notify(new LeaveRequestRejected($approval->leave_id, Auth::user()->name));

        return back()->with('success', 'Approval ditolak.');
    }

    // proteksi supaya hanya approver terkait yang bisa bertindak & tidak lompat step
    private function authorizeApproval(Approval $approval): void
    {
        abort_unless($approval->approver_id === Auth::id(), 403);

        $prev = $approval->leave->approvals->where('step', '<', $approval->step);
        abort_unless($prev->every(fn($x) => $x->status === 'approved'), 403);
        abort_if($approval->status !== 'pending', 400);
    }

    private function finalApprove($leave)
    {
        app(LeaveApprovalService::class)->finalApprove($leave);
    }

    private function calculateWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        while ($start <= $end) {
            if ($start->dayOfWeek !== Carbon::SATURDAY && $start->dayOfWeek !== Carbon::SUNDAY) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }
}
