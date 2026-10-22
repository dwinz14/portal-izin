<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use App\Models\User;
use App\Models\UserLeaveBalance;
use App\Notifications\LeaveRequestSubmitted;
use App\Notifications\RevisionAccepted;
use App\Notifications\RevisionRejected;
use App\Services\LeaveApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;
use App\Services\ImageCompressionService;
use Illuminate\Support\Facades\Gate;
use App\Exceptions\LeaveRuleViolation;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRulesService;
use App\Services\PenggantiEligibilityService;

class LeaveController extends Controller
{
    public function __construct(
        protected LeaveRulesService $rules,
        protected LeaveBalanceService $balances,
        protected PenggantiEligibilityService $eligibility,
        protected ImageCompressionService $imageCompressor,
    ) {}

    public function index()
    {
        $leaves = Leave::with('approvals.approver')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(5);

        return view('leaves.index', compact('leaves'));
    }

    public function create()
    {
        $user = Auth::user();

        // Aturan pengganti per role
        $replacementMode     = $user->replacementRequirement();
        $showReplacement     = $replacementMode !== User::REPLACEMENT_NONE;
        $replacementOptional = $replacementMode === User::REPLACEMENT_OPTIONAL;

        // Daftar calon pengganti
        $penggantiList = $showReplacement
            ? $this->eligibility->forRequester($user)
            : collect();

        $requiresAtasan = ! in_array($user->role, ['direksi'], true);
        $atasanList = collect();

        if ($requiresAtasan) {
            $direksi = User::select('id', 'name', 'role')
                ->where('role', 'direksi')
                ->where('id', '!=', $user->id)
                ->get();

            $atasanList = $atasanList->merge($direksi);

            $hrd = User::select('id', 'name', 'role')
                ->where('role', 'hrd')
                ->where('id', '!=', $user->id)
                ->get();

            $atasanList = $atasanList->merge($hrd);

            if ($user->role !== 'hrd') {
                $others = User::select('id', 'name', 'role')
                    ->where('office_id', $user->office_id)
                    ->whereIn('role', ['kabag-pincab'])
                    ->where('id', '!=', $user->id)
                    ->get();

                $atasanList = $atasanList->merge($others);

                $atasanList = $atasanList->unique('id')->values();
            }
        }

        // Get available leave types for the user
        $leaveTypes = LeaveType::where('is_active', true)
            ->when($user->gender, function ($query) use ($user) {
                return $query->where(function ($q) use ($user) {
                    $q->whereNull('gender')
                        ->orWhere('gender', $user->gender);
                });
            })
            ->where(function ($q) use ($user) {
                $masaKerja = $user->masaKerjaTahun();

                $q->where('min_years', 0)
                    ->orWhere('min_years', '<=', $masaKerja);
            })
            ->get();

        // Get user's leave balances for current year
        $userLeaveBalances = UserLeaveBalance::where('user_id', $user->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get()
            ->keyBy('leave_type_id');

        // Data hari libur (tanggal merah & cuti bersama) untuk menandai kalender
        $holidayMap = PublicHoliday::getHolidayMap();

        return view('leaves.create', compact('penggantiList', 'showReplacement', 'replacementOptional', 'atasanList', 'requiresAtasan', 'leaveTypes', 'userLeaveBalances', 'user', 'holidayMap'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $user = Auth::user();

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $penggantiId = $user->replacementRequirement() === User::REPLACEMENT_NONE
            ? null
            : ($request->pengganti_id ?: null);

        // Validasi masa kerja minimal untuk jenis cuti
        if ($leaveType->min_years > 0) {
            if (! $user->tanggal_aktif_kerja) {
                return back()->withErrors([
                    'leave_type_id' => 'Tanggal aktif kerja belum diatur. Hubungi Admin.',
                ])->withInput();
            }

            $masaKerjaTahun = $user->masaKerjaTahun();
            if ($masaKerjaTahun < $leaveType->min_years) {
                return back()->withErrors([
                    'leave_type_id' => "Jenis cuti ini hanya untuk karyawan dengan masa kerja minimal {$leaveType->min_years} tahun.",
                ])->withInput();
            }
        }

        $isSickLeave = in_array(strtolower($leaveType->name), ['izin sakit dengan surat dokter', 'izin sakit tanpa surat dokter']);
        $requiresProof = strtolower($leaveType->name) === 'izin sakit dengan surat dokter';

        // Validasi ketersediaan jenis cuti untuk user
        if ($leaveType->gender && $leaveType->gender !== $user->gender) {
            return back()->withErrors(['leave_type_id' => 'Jenis cuti ini tidak tersedia untuk Anda.'])->withInput();
        }

        try {
            $totalHari = $this->rules->assertSchedulable(
                $user->id,
                $leaveType,
                $request->start_date,
                $request->end_date,
                $penggantiId ? (int) $penggantiId : null,
            );
        } catch (LeaveRuleViolation $e) {
            return back()->withErrors(['msg' => $e->getMessage()])->withInput();
        }

        $proofImagePath = null;
        if ($requiresProof && $request->hasFile('proof_image')) {
            try {
                $proofImagePath = $this->imageCompressor->compress($request->file('proof_image'), 'proof_images');
            } catch (\RuntimeException $e) {
                return back()->withErrors(['proof_image' => $e->getMessage()])->withInput();
            }
        }

        try {
            // Transaction untuk insert data
            return DB::transaction(function () use ($request, $user, $totalHari, $leaveType, $isSickLeave, $proofImagePath, $penggantiId) {

                // Buat record cuti
                $leave = Leave::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $request->leave_type_id,
                    'pengganti_id' => $penggantiId,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_hari' => $totalHari,
                    'alasan' => $request->alasan,
                    'proof_image' => $proofImagePath,
                    'status_final' => 'pending',
                    'is_mendadak' => ! $isSickLeave && \Carbon\Carbon::parse($request->start_date)->lt(\Carbon\Carbon::today()->addWeek()),
                ]);

                // Log setelah leave berhasil dibuat
                ActivityLogger::log(
                    'leave.submitted',
                    "Mengajukan {$leaveType->name} {$leave->total_hari} hari kerja (" .
                        \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') . ' s/d ' .
                        \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') . ')',
                    $leave,
                    [
                        'jenis_cuti' => $leaveType->name,
                        'total_hari' => $leave->total_hari,
                        'start_date' => $leave->start_date,
                        'end_date'   => $leave->end_date,
                    ] + (
                        // Pemohon sebenarnya boleh memilih tanpa pengganti
                        ($penggantiId === null && $user->replacementRequirement() !== User::REPLACEMENT_NONE)
                        ? ['tanpa_pengganti' => 'ya']
                        : []
                    )
                );

                // Kasus khusus: role direksi -> langsung disetujui
                if ($user->role === 'direksi') {
                    $leave->update(['status_final' => 'approved']);

                    $this->balances->deduct($leave);

                    ApprovalHistory::create([
                        'leave_id' => $leave->id,
                        'approved_by' => $user->id,
                        'role' => $user->role,
                        'status' => 'approved',
                    ]);

                    return redirect()->route('cuti.index')->with('success', 'Pengajuan cuti disetujui otomatis.');
                }

                // Buat daftar approver (pengganti dan atasan)
                $atasanId = $request->atasan_id;
                $approvers = collect([$penggantiId, $atasanId])->filter()->values();

                // Simpan approval steps
                foreach ($approvers as $index => $approverId) {
                    Approval::create([
                        'leave_id' => $leave->id,
                        'approver_id' => $approverId,
                        'step' => $index + 1,
                        'status' => 'pending',
                    ]);
                }

                // Notifikasi ke approver pertama
                $firstApprover = User::find($approvers->first());
                if ($firstApprover) {
                    $firstApprover->notify(new \App\Notifications\LeaveRequestSubmitted($leave));
                }

                // Kasus khusus: jika pengganti == atasan, maka step1 langsung auto-approve
                if ($penggantiId && $atasanId && $penggantiId === $atasanId) {
                    $step1 = $leave->approvals()->where('step', 1)->first();
                    if ($step1) {
                        $step1->update(['status' => 'approved']);
                    }

                    ApprovalHistory::create([
                        'leave_id' => $leave->id,
                        'approved_by' => $atasanId,
                        'role' => User::find($atasanId)->role,
                        'step' => 1,
                        'status' => 'approved',
                        'catatan' => 'Auto-approved: pengganti dan atasan adalah orang yang sama.',
                    ]);

                    // Notifikasi ke atasan (yang juga sebagai pengganti)
                    $step2Exists = $leave->approvals()->where('step', 2)->exists();
                    if (! $step2Exists) {
                        Approval::create([
                            'leave_id' => $leave->id,
                            'approver_id' => $atasanId,
                            'step' => 2,
                            'status' => 'pending',
                        ]);
                    }

                    // Kirim notifikasi ke atasan untuk step2
                    $atasan = User::find($atasanId);
                    if ($atasan) {
                        $atasan->notify(new \App\Notifications\LeaveRequestSubmitted($leave));
                    }
                }

                return redirect()->route('cuti.index')->with('success', 'Pengajuan cuti berhasil dibuat.');
            });
        } catch (\Throwable $e) {
            $this->imageCompressor->delete($proofImagePath);
            throw $e;
        }
    }

    public function destroy(Leave $leave)
    {
        DB::transaction(function () use ($leave) {

            $locked = Leave::with('leaveType')->lockForUpdate()->findOrFail($leave->id);

            Gate::authorize('delete', $locked);

            ActivityLogger::log(
                'leave.cancelled',
                "Membatalkan pengajuan {$locked->leaveType?->name} (" .
                    \Carbon\Carbon::parse($locked->start_date)->format('d/m/Y') . ' s/d ' .
                    \Carbon\Carbon::parse($locked->end_date)->format('d/m/Y') . ')',
                $locked,
                [
                    'jenis_cuti' => $locked->leaveType?->name,
                    'start_date' => $locked->start_date,
                    'end_date'   => $locked->end_date,
                ]
            );

            $locked->delete();
        });

        return redirect()->route('cuti.index')->with('success', 'Pengajuan cuti berhasil dihapus.');
    }

    public function replacements()
    {
        $leaves = Leave::with('user')
            ->where('pengganti_id', Auth::id())
            ->where('status_final', 'approved')
            ->orderByRaw("
        CASE
            WHEN start_date <= CURDATE()
             AND end_date >= CURDATE() THEN 0
            WHEN start_date > CURDATE() THEN 1
            ELSE 2
        END
    ")
            ->orderBy('start_date', 'asc')
            ->paginate(10);

        return view('replacements.index', compact('leaves'));
    }

    public function acceptRevision(Leave $leave)
    {
        abort_unless($leave->user_id === Auth::id(), 403);
        abort_unless($leave->is_revision_pending, 400);

        $approval = $leave->revisionApproval;

        if (! $approval || ! $approval->revised_start_date) {
            return back()->withErrors(['msg' => 'Data revisi tidak ditemukan.']);
        }

        // Update tanggal leave dengan tanggal revisi
        $leave->update([
            'start_date' => $approval->revised_start_date,
            'end_date' => $approval->revised_end_date,
            'total_hari' => $approval->revised_total_hari,
            'is_revision_pending' => false,
            'revision_by_approval_id' => null,
        ]);

        // Approve approval yang meminta revisi
        $approval->update([
            'status' => 'approved',
        ]);

        // Update history status for the approver
        ApprovalHistory::where('leave_id', $leave->id)
            ->where('approved_by', $approval->approver_id)
            ->where('status', 'revision_requested')
            ->update(['status' => 'revision_accepted']);

        ApprovalHistory::create([
            'leave_id' => $leave->id,
            'approved_by' => Auth::id(),
            'role' => Auth::user()->role,
            'step' => null,
            'status' => 'revision_accepted',
            'catatan' => "Pemohon menyetujui revisi tanggal: {$approval->revised_start_date} s/d {$approval->revised_end_date}",
        ]);

        // Kirim notifikasi ke approver
        $approver = User::find($approval->approver_id);
        $approver->notify(new RevisionAccepted($leave, Auth::user()->name));;

        // Cek apakah ada approver berikutnya
        $nextApproval = $leave->approvals()->where('step', '>', $approval->step)->orderBy('step')->first();

        if ($nextApproval) {
            // Kirim notifikasi ke approver berikutnya
            $nextApprover = User::find($nextApproval->approver_id);
            if ($nextApprover) {
                $nextApprover->notify(new LeaveRequestSubmitted($leave));
            }
        } else {
            // Final approve
            $this->finalApproveLeave($leave);
        }

        ActivityLogger::log(
            'leave.revision_accepted',
            'Menerima revisi tanggal cuti dari ' . ucwords($approval->approver?->name ?? 'atasan') .
                ' — ' . \Carbon\Carbon::parse($approval->revised_start_date)->format('d/m/Y') .
                ' s/d ' . \Carbon\Carbon::parse($approval->revised_end_date)->format('d/m/Y'),
            $leave,
            [
                'new_start'  => $approval->revised_start_date,
                'new_end'    => $approval->revised_end_date,
                'new_hari'   => $approval->revised_total_hari,
                'dari_atasan' => $approval->approver?->name,
            ]
        );

        return redirect()->route('cuti.index')->with('success', 'Revisi tanggal telah diterima dan cuti disetujui.');
    }

    public function rejectRevision(Leave $leave)
    {
        abort_unless($leave->user_id === Auth::id(), 403);
        abort_unless($leave->is_revision_pending, 400);

        $approval = $leave->revisionApproval;

        // Update status
        $leave->update([
            'status_final' => 'rejected',
            'is_revision_pending' => false,
            'revision_by_approval_id' => null,
        ]);

        $approval->update([
            'status' => 'rejected',
        ]);

        // Update history status for the approver
        ApprovalHistory::where('leave_id', $leave->id)
            ->where('approved_by', $approval->approver_id)
            ->where('status', 'revision_requested')
            ->update(['status' => 'revision_rejected']);

        ApprovalHistory::create([
            'leave_id' => $leave->id,
            'approved_by' => Auth::id(),
            'role' => Auth::user()->role,
            'step' => null,
            'status' => 'revision_rejected',
            'catatan' => 'Pemohon menolak revisi tanggal dari ' . $approval->approver->name,
        ]);

        // Kirim notifikasi ke approver
        $approver = User::find($approval->approver_id);
        $approver->notify(new RevisionRejected($leave, Auth::user()->name));

        ActivityLogger::log(
            'leave.revision_rejected',
            'Menolak revisi tanggal cuti dari ' . ucwords($approval->approver?->name ?? 'atasan'),
            $leave,
            ['dari_atasan' => $approval->approver?->name]
        );
        return redirect()->route('cuti.index')->with('error', 'Revisi tanggal ditolak. Pengajuan cuti dibatalkan.');
    }

    private function finalApproveLeave($leave)
    {
        app(LeaveApprovalService::class)->finalApprove($leave);
    }
}
