<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\AttendanceRequest;
use App\Models\Office;
use App\Models\User;
use App\Notifications\AttendanceRequestSubmitted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    public function index()
    {
        $attendanceRequests = AttendanceRequest::with('approver')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('attendance-requests.index', compact('attendanceRequests'));
    }

    public function create()
    {
        $user = Auth::user();
        $approverList = $this->getApproverList($user);
        $typeLabels = AttendanceRequest::typeLabels();

        return view('attendance-requests.create', compact('approverList', 'typeLabels'));
    }

    public function store(StoreAttendanceRequest $request)
    {
        $user = Auth::user();
        $approverList = $this->getApproverList($user);

        if (! $approverList->pluck('id')->contains((int) $request->approver_id)) {
            return back()
                ->withErrors(['approver_id' => 'Atasan yang dipilih tidak tersedia untuk user Anda.'])
                ->withInput();
        }

        $hasDuplicate = AttendanceRequest::where('user_id', $user->id)
            ->where('type', $request->type)
            ->whereDate('date', $request->date)
            ->where('status', AttendanceRequest::STATUS_PENDING)
            ->exists();

        if ($hasDuplicate) {
            return back()
                ->withErrors(['msg' => 'Anda masih memiliki pengajuan kehadiran yang sama pada tanggal tersebut dan sedang diproses.'])
                ->withInput();
        }

        return DB::transaction(function () use ($request, $user) {
            $proofImagePath = null;

            if ($request->hasFile('proof_image')) {
                $proofImagePath = $request->file('proof_image')->store('attendance_proofs', 'public');
            }

            $attendanceRequest = AttendanceRequest::create([
                'user_id' => $user->id,
                'approver_id' => $request->approver_id,
                'type' => $request->type,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'reason' => $request->reason,
                'proof_image' => $proofImagePath,
                'status' => AttendanceRequest::STATUS_PENDING,
            ]);

            $attendanceRequest->approver?->notify(new AttendanceRequestSubmitted($attendanceRequest));

            return redirect()
                ->route('kehadiran.index')
                ->with('success', 'Pengajuan kehadiran berhasil dikirim.');
        });
    }

    public function destroy(AttendanceRequest $kehadiran)
    {
        abort_unless($kehadiran->user_id === Auth::id(), 403);
        abort_unless($kehadiran->status === AttendanceRequest::STATUS_PENDING, 400);

        $kehadiran->delete();

        return redirect()
            ->route('kehadiran.index')
            ->with('success', 'Pengajuan kehadiran berhasil dibatalkan.');
    }

    private function getApproverList(User $user)
    {
        if ($user->role === 'direksi') {
            return collect();
        }

        $approverList = collect();

        $approverList = $approverList->merge(
            Cache::remember('direksi_users', 300, fn () => User::select('id', 'name', 'role')->where('role', 'direksi')->get())
        );

        $approverList = $approverList->merge(
            Cache::remember('hrd_users', 300, fn () => User::select('id', 'name', 'role')->where('role', 'hrd')->get())
        );

        if ($user->role !== 'hrd') {
            $approverList = $approverList->merge(
                Cache::remember("atasan_{$user->office_id}", 300, fn () => User::select('id', 'name', 'role')
                    ->where('office_id', $user->office_id)
                    ->whereIn('role', ['kabag-pincab', 'kasie'])
                    ->where('id', '!=', $user->id)
                    ->get())
            );
        }

        if ($user->role === 'kabag-pincab' && $user->office_id == Office::PUSAT) {
            $approverList = $approverList->merge(
                User::select('id', 'name', 'role')
                    ->where('office_id', Office::PUSAT)
                    ->where('id', '!=', $user->id)
                    ->whereIn('role', ['direksi', 'hrd'])
                    ->get()
            );
        }

        return $approverList->unique('id')->values();
    }
}
