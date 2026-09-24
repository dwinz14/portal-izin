<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLeavePenggantiRequest;
use App\Models\Leave;
use App\Models\LeavePenggantiChange;
use App\Models\User;
use App\Notifications\PenggantiAssigned;
use App\Notifications\PenggantiChangedForRequester;
use App\Notifications\PenggantiReleased;
use App\Services\ActivityLogger;
use App\Services\LeaveOverlapChecker;
use App\Services\PenggantiEligibilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LeaveReplacementController extends Controller
{
    public function __construct(protected LeaveOverlapChecker $overlapChecker) {}

    /**
     * Daftar cuti approved di bawah tanggung jawab atasan yang login
     * (sebagai approver step-2), yang masih bisa diganti penggantinya
     * (belum melewati end_date).
     */
    public function index()
    {
        $leaves = Leave::with(['user', 'pengganti', 'leaveType'])
            ->where('status_final', 'approved')
            ->whereNotNull('pengganti_id')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereHas('approvals', function ($q) {
                $q->where('step', 2)
                    ->where('approver_id', Auth::id())
                    ->where('status', 'approved');
            })
            ->orderBy('start_date')
            ->paginate(10);

        return view('replacements.manage', compact('leaves'));
    }

    /**
     * Daftar calon pengganti (sesuai aturan role/kantor pemohon) untuk
     * ditampilkan di modal, dipakai lewat AJAX dari halaman index.
     */
    public function eligiblePengganti(Leave $leave, PenggantiEligibilityService $eligibility)
    {
        Gate::authorize('changePengganti', $leave);

        $candidates = $eligibility->forRequester($leave->user)
            ->reject(fn(User $u) => in_array($u->id, [$leave->pengganti_id, $leave->user_id], true))
            ->values();

        return response()->json($candidates->map(fn($u) => [
            'id'   => $u->id,
            'name' => $u->name,
        ]));
    }

    /**
     * Proses penggantian user pengganti. Otorisasi & validasi struktural
     * sudah ditangani UpdateLeavePenggantiRequest (LeavePolicy::changePengganti
     * + aturan eligibility + wajib ketik "GANTI").
     */
    public function update(UpdateLeavePenggantiRequest $request, Leave $leave)
    {
        $newPenggantiId = (int) $request->validated('new_pengganti_id');
        $atasan = Auth::user();

        $result = DB::transaction(function () use ($leave, $newPenggantiId, $atasan) {
            // Lock baris leave supaya aman dari race condition (mis. double submit).
            $lockedLeave = Leave::where('id', $leave->id)->lockForUpdate()->firstOrFail();

            // Defense-in-depth: state bisa berubah antara form dimuat & disubmit.
            abort_unless($lockedLeave->status_final === 'approved', 422, 'Cuti ini sudah tidak berstatus approved.');
            abort_unless($lockedLeave->pengganti_id, 422, 'Cuti ini belum punya pengganti.');
            abort_if(
                Carbon::today()->gt(Carbon::parse($lockedLeave->end_date)),
                422,
                'Periode cuti ini sudah lewat, penggantinya tidak bisa diganti lagi.'
            );

            // Idempotency guard: kalau sudah diganti user lain jadi nilai yang sama
            // (mis. akibat double-submit), tidak perlu proses ulang.
            if ((int) $lockedLeave->pengganti_id === $newPenggantiId) {
                return ['changed' => false];
            }

            // Cek overlap jadwal pengganti baru (reuse checker yang sama dengan pengajuan awal),
            // exclude leave ini sendiri dari pengecekan.
            if (
                $this->overlapChecker->hasReplacementOnLeave($newPenggantiId, $lockedLeave->start_date, $lockedLeave->end_date, $lockedLeave->id)
                || $this->overlapChecker->hasOverlapReplacement($newPenggantiId, $lockedLeave->start_date, $lockedLeave->end_date, $lockedLeave->id)
            ) {
                abort(422, 'Calon pengganti baru sedang bentrok jadwal (cuti sendiri atau sudah jadi pengganti di cuti lain) pada periode ini.');
            }

            $oldPenggantiId = $lockedLeave->pengganti_id;
            $oldPengganti   = $lockedLeave->pengganti;
            $newPengganti   = User::findOrFail($newPenggantiId);

            // Audit trail
            LeavePenggantiChange::create([
                'leave_id'         => $lockedLeave->id,
                'old_pengganti_id' => $oldPenggantiId,
                'new_pengganti_id' => $newPengganti->id,
                'changed_by'       => $atasan->id,
            ]);

            $lockedLeave->update(['pengganti_id' => $newPengganti->id]);

            ActivityLogger::log(
                'approval.pengganti_changed',
                "Mengubah pengganti izin/cuti {$lockedLeave->user->name} dari " .
                    ($oldPengganti->name ?? '-') . " menjadi {$newPengganti->name}",
                $lockedLeave,
            );

            // Notifikasi ke 3 pihak
            $lockedLeave->user->notify(new PenggantiChangedForRequester(
                $lockedLeave,
                $atasan->name,
                $oldPengganti->name ?? '-',
                $newPengganti->name
            ));

            if ($oldPengganti) {
                $oldPengganti->notify(new PenggantiReleased($lockedLeave, $atasan->name, $newPengganti->name));
            }

            $newPengganti->notify(new PenggantiAssigned($lockedLeave, $atasan->name));

            return [
                'changed' => true,
                'old'     => $oldPengganti->name ?? '-',
                'new'     => $newPengganti->name,
            ];
        });

        if (! $result['changed']) {
            return redirect()->route('cuti.replacement.index')
                ->with('info', 'Tidak ada perubahan — pengganti yang dipilih sudah menjadi pengganti saat ini.');
        }

        return redirect()->route('cuti.replacement.index')
            ->with('success', "Pengganti berhasil diganti dari {$result['old']} menjadi {$result['new']}.");
    }
}
