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
use App\Services\ActivityLogger;

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
        $user        = Auth::user();
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

            $isUpdateAttendance = $request->type === AttendanceRequest::TYPE_UPDATE_ATTENDANCE;
            $updateType         = $isUpdateAttendance ? $request->update_type : null;

            // Tentukan start_time dan end_time berdasarkan update_type
            $startTime = null;
            $endTime   = null;

            if ($isUpdateAttendance) {
                $startTime = $updateType !== AttendanceRequest::UPDATE_TYPE_CHECKOUT_ONLY
                    ? $request->start_time
                    : null;

                $endTime = in_array($updateType, [
                    AttendanceRequest::UPDATE_TYPE_BOTH,
                    AttendanceRequest::UPDATE_TYPE_CHECKOUT_ONLY,
                ]) ? $request->end_time : null;
            } else {
                $startTime = $request->start_time;
                $endTime   = $request->end_time;
            }

            $attendanceRequest = AttendanceRequest::create([
                'user_id'     => $user->id,
                'approver_id' => $request->approver_id,
                'type'        => $request->type,
                'update_type' => $updateType,
                'date'        => $request->date,
                'start_time'  => $startTime,
                'end_time'    => $endTime,
                'reason'      => $request->reason,
                'proof_image' => $proofImagePath,
                'status'      => AttendanceRequest::STATUS_PENDING,
            ]);

            $attendanceRequest->approver?->notify(new AttendanceRequestSubmitted($attendanceRequest));

            ActivityLogger::log(
                'attendance.submitted',
                'Mengajukan ' . $attendanceRequest->type_label .
                    ($updateType ? ' (' . $attendanceRequest->update_type_label . ')' : '') .
                    ' pada ' . \Carbon\Carbon::parse($attendanceRequest->date)->format('d/m/Y'),
                $attendanceRequest,
                [
                    'type'        => $attendanceRequest->type_label,
                    'update_type' => $attendanceRequest->update_type_label,
                    'date'        => $attendanceRequest->date,
                ]
            );

            return redirect()
                ->route('kehadiran.index')
                ->with('success', 'Pengajuan kehadiran berhasil dikirim.');
        });
    }

    public function destroy(AttendanceRequest $kehadiran)
    {
        abort_unless($kehadiran->user_id === Auth::id(), 403);
        abort_unless($kehadiran->status === AttendanceRequest::STATUS_PENDING, 400);

        ActivityLogger::log(
            'attendance.cancelled',
            'Membatalkan pengajuan ' . $kehadiran->type_label .
                ' pada ' . \Carbon\Carbon::parse($kehadiran->date)->format('d/m/Y'),
            $kehadiran,
            ['type' => $kehadiran->type_label, 'date' => $kehadiran->date]
        );

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
            Cache::remember('direksi_users', 300, fn() => User::select('id', 'name', 'role')->where('role', 'direksi')->get())
        );

        $approverList = $approverList->merge(
            Cache::remember('hrd_users', 300, fn() => User::select('id', 'name', 'role')->where('role', 'hrd')->get())
        );

        if ($user->role !== 'hrd') {
            $approverList = $approverList->merge(
                Cache::remember("atasan_{$user->office_id}", 300, fn() => User::select('id', 'name', 'role')
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
