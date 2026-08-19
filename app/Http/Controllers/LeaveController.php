<?php

namespace App\Http\Controllers;

use App\Helpers\LeaveHelper;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Office;
use App\Models\PublicHoliday;
use App\Models\User;
use App\Models\UserLeaveBalance;
use App\Notifications\LeaveRequestSubmitted;
use App\Notifications\RevisionAccepted;
use App\Notifications\RevisionRejected;
use App\Services\LeaveApprovalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
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

        $activePending = Leave::where('user_id', $user->id)
            ->where('status_final', 'pending')
            ->exists();

        if ($activePending) {
            return redirect()->route('cuti.index')
                ->with('error', 'Anda masih memiliki pengajuan cuti yang sedang diproses. Silakan tunggu hingga selesai.');
        }

        $requiresReplacement = in_array($user->role, ['staff', 'kasie', 'kabag-pincab'], true);

        // $penggantiList = $requiresReplacement
        //     ? Cache::remember("pengganti_{$user->office_id}", 300, fn() =>
        //     User::select('id', 'name', 'role')->where('office_id', $user->office_id)->where('id', '!=', $user->id)->get())
        //     : collect();
        // Default query dasar
        $query = User::query()->where('id', '!=', $user->id);

        // Case 1: user role kabag-pincab & kantor pusat
        if ($user->role === 'kabag-pincab' && $user->office_id == Office::PUSAT) {
            $penggantiList = $query
                ->where('office_id', Office::PUSAT)
                ->orderBy('name')
                ->get();

            // Case 2: user role kabag-pincab tapi bukan kantor pusat
        } elseif ($user->role === 'kabag-pincab' && $user->office_id != Office::PUSAT) {
            $penggantiList = $query
                ->whereIn('role', ['kabag-pincab', 'hrd'])
                ->orderBy('name')
                ->get();

            // Case 3: role lain → tetap filter satu kantor
        } else {
            $penggantiList = $requiresReplacement
                ? Cache::remember("pengganti_{$user->office_id}", 300, fn () => User::select('id', 'name', 'role')->where('office_id', $user->office_id)->where('id', '!=', $user->id)->get())
                : collect();
        }
        $requiresAtasan = ! in_array($user->role, ['direksi'], true);
        $atasanList = collect();

        if ($requiresAtasan) {
            $direksi = Cache::remember('direksi_users', 300, fn () => User::select('id', 'name', 'role')->where('role', 'direksi')->get());

            $atasanList = $atasanList->merge($direksi);

            $hrd = Cache::remember('hrd_users', 300, fn () => User::select('id', 'name', 'role')->where('role', 'hrd')->get());

            $atasanList = $atasanList->merge($hrd);

            if ($user->role !== 'hrd') {
                $others = Cache::remember("atasan_{$user->office_id}", 300, fn () => User::select('id', 'name', 'role')
                    ->where('office_id', $user->office_id)
                    ->whereIn('role', ['kabag-pincab', 'kasie'])
                    ->where('id', '!=', $user->id)
                    ->get());

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

        return view('leaves.create', compact('penggantiList', 'requiresReplacement', 'atasanList', 'requiresAtasan', 'leaveTypes', 'userLeaveBalances', 'user', 'holidayMap'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $user = Auth::user();

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

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

        $totalHari = LeaveHelper::calculateWorkingDays($request->start_date, $request->end_date);

        // Tolak pengajuan yang tidak memiliki hari kerja sama sekali
        // (misal: seluruh rentang jatuh di akhir pekan / libur nasional / cuti bersama)
        if ($totalHari <= 0) {
            return back()->withErrors(['msg' => 'Rentang tanggal yang dipilih tidak memiliki hari kerja (seluruhnya akhir pekan, libur nasional, atau cuti bersama).'])->withInput();
        }

        // Cek kuota (jika cuti memiliki batasan kuota)
        if ($leaveType->quota > 0) {
            $userLeaveBalance = UserLeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if (! $userLeaveBalance || $userLeaveBalance->remaining < $totalHari) {
                return back()->withErrors(['msg' => 'Kuota cuti tidak mencukupi untuk jenis cuti ini.'])->withInput();
            }
        }

        // Cek apakah user masih punya pengajuan aktif
        if ($this->hasActivePending($user->id)) {
            return back()->withErrors(['msg' => 'Anda masih memiliki pengajuan cuti yang sedang diproses. Selesaikan terlebih dahulu sebelum mengajukan yang baru.']);
        }

        // Cek tumpang tindih cuti dengan user sendiri
        if ($this->hasOverlapLeave($user->id, $request->start_date, $request->end_date)) {
            return back()->withErrors(['msg' => 'Tanggal yang dipilih bertabrakan dengan cuti yang sudah disetujui.']);
        }

        // Cek tumpang tindih cuti dengan pengganti
        if ($request->pengganti_id && $this->hasOverlapLeave($request->pengganti_id, $request->start_date, $request->end_date)) {
            return back()->withErrors(['msg' => 'Pengganti yang dipilih sedang cuti di tanggal tersebut.']);
        }

        // Cek apakah pengganti sudah ditugaskan di cuti lain
        if ($request->pengganti_id && $this->hasOverlapReplacement($request->pengganti_id, $request->start_date, $request->end_date)) {
            return back()->withErrors(['msg' => 'Pengganti tersebut sudah ditugaskan pada cuti lain.']);
        }

        // Cek apakah user sedang menjadi pengganti untuk cuti orang lain
        if ($this->hasOverlapReplacement($user->id, $request->start_date, $request->end_date)) {
            return back()->withErrors(['msg' => 'Anda sedang jadi pengganti di tanggal tersebut.']);
        }

        // Transaction untuk insert data
        return DB::transaction(function () use ($request, $user, $totalHari, $leaveType, $isSickLeave) {
            $proofImagePath = null;
            if ($request->hasFile('proof_image')) {
                $proofImagePath = $request->file('proof_image')->store('proof_images', 'public');
            }

            // Buat record cuti
            $leave = Leave::create([
                'user_id' => $user->id,
                'leave_type_id' => $request->leave_type_id,
                'pengganti_id' => $request->pengganti_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_hari' => $totalHari,
                'alasan' => $request->alasan,
                'proof_image' => $proofImagePath,
                'status_final' => 'pending',
                'is_mendadak' => ! $isSickLeave && \Carbon\Carbon::parse($request->start_date)->lt(\Carbon\Carbon::today()->addWeek()),
            ]);

            // Kasus khusus: role direksi -> langsung disetujui
            if ($user->role === 'direksi') {
                $leave->update(['status_final' => 'approved']);

                if ($leaveType->quota > 0) {
                    $balance = UserLeaveBalance::where('user_id', $user->id)
                        ->where('leave_type_id', $request->leave_type_id)
                        ->where('year', now()->year)
                        ->first();

                    if ($balance) {
                        $balance->update([
                            'used' => \DB::raw("used + {$totalHari}"),
                            'remaining' => \DB::raw("remaining - {$totalHari}"),
                        ]);
                    }
                }

                ApprovalHistory::create([
                    'leave_id' => $leave->id,
                    'approved_by' => $user->id,
                    'role' => $user->role,
                    'status' => 'approved',
                ]);

                return redirect()->route('cuti.index')->with('success', 'Pengajuan cuti disetujui otomatis.');
            }

            // Buat daftar approver (pengganti dan atasan)
            $penggantiId = $request->pengganti_id;
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
                $firstApprover->notify(new \App\Notifications\LeaveRequestSubmitted($leave->id, $user->name));
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

                // Notifikasi ke atasan (yang juga sebagai pengganti), tetapi sekarang atasan menjadi approver step2
                // Step2 sudah ada jika $approvers berisi 1 elemen? Jika pengganti==atasan, $approvers hanya satu elemen.
                // Jadi perlu dibuat approval step2 sendiri? Dari kode di atas, $approvers hanya satu karena filter->values() akan unik.
                // Karena itu, kita perlu menambahkan step2 secara manual jika diperlukan. Namun karena sistem approval mengharapkan 2 step,
                // maka perlu penanganan khusus: jika hanya 1 approver, maka langsung final approve. Atau buat step2 dengan approver yang sama.
                // Sesuai logika asli di ApprovalController, jika step1 auto-approve, maka atasan tetap menerima notifikasi sebagai step2.
                // Karena $approvers hanya berisi satu user, kita harus menambahkan step2 untuk user yang sama.
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
                    $atasan->notify(new \App\Notifications\LeaveRequestSubmitted($leave->id, $user->name));
                }
            }

            return redirect()->route('cuti.index')->with('success', 'Pengajuan cuti berhasil dibuat.');
        });
    }

    /**
     * Cek apakah user memiliki pengajuan dengan status_final pending.
     */
    private function hasActivePending(int $userId): bool
    {
        return Leave::where('user_id', $userId)
            ->where('status_final', 'pending')
            ->exists();
    }

    private function hasOverlapLeave(int $userId, string $start, string $end): bool
    {
        return Leave::where('user_id', $userId)
            ->where('status_final', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();
    }

    private function hasOverlapReplacement($replacementId, $start, $end)
    {
        return Leave::where('pengganti_id', $replacementId)
            ->whereNotIn('status_final', ['rejected'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();
    }

    public function destroy(Leave $leave)
    {
        $leave->delete();

        return redirect()->route('cuti.index')->with('success', 'Pengajuan cuti berhasil dihapus.');
    }

    public function replacements()
    {
        $leaves = Leave::with('user')
            ->where('pengganti_id', Auth::id())
            ->where('status_final', 'approved')  // ← tambah ini
            ->latest()
            ->paginate(10);

        return view('replacements.index', compact('leaves'));
    }

    private function calculateWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        while ($start <= $end) {
            // Check if it's a weekday (Monday to Friday)
            if ($start->dayOfWeek !== Carbon::SATURDAY && $start->dayOfWeek !== Carbon::SUNDAY) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
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
        $approver->notify(new RevisionAccepted($leave->id, Auth::user()->name));

        // Cek apakah ada approver berikutnya
        $nextApproval = $leave->approvals()->where('step', '>', $approval->step)->orderBy('step')->first();

        if ($nextApproval) {
            // Kirim notifikasi ke approver berikutnya
            $nextApprover = User::find($nextApproval->approver_id);
            if ($nextApprover) {
                $nextApprover->notify(new LeaveRequestSubmitted($leave->id, $leave->user->name));
            }
        } else {
            // Final approve
            $this->finalApproveLeave($leave);
        }

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
            'catatan' => 'Pemohon menolak revisi tanggal dari '.$approval->approver->name,
        ]);

        // Kirim notifikasi ke approver
        $approver = User::find($approval->approver_id);
        $approver->notify(new RevisionRejected($leave->id, Auth::user()->name));

        return redirect()->route('cuti.index')->with('error', 'Revisi tanggal ditolak. Pengajuan cuti dibatalkan.');
    }

    private function finalApproveLeave($leave)
    {
        app(LeaveApprovalService::class)->finalApprove($leave);
    }
}
