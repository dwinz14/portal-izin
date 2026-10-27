<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteApprovedAttendanceRequest;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Notifications\AttendanceRequestDeleted;
use App\Services\AttendanceDeletionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Kelola pengajuan kehadiran yang sudah final approved (oleh atasan yang menyetujuinya).
 *
 * Controller sengaja tipis: otorisasi + validasi input ada di Form Request,
 * aturan bisnis & transaksi ada di AttendanceDeletionService.
 */
class AttendanceManagementController extends Controller
{
    public function __construct(
        protected AttendanceDeletionService $deletion,
    ) {}

    /**
     * Daftar pengajuan kehadiran approved yang boleh dihapus
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q'         => ['nullable', 'string', 'max:100'],
            'type'      => ['nullable', Rule::in(AttendanceRequest::TYPES)],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $attendanceRequests = AttendanceRequest::with(['user.position', 'user.office'])
            ->deletableBy($request->user())
            ->when($filters['q'] ?? null, function ($query, $keyword) {
                // Escape wildcard LIKE agar input "%" atau "_" tidak dianggap pola
                $keyword = addcslashes($keyword, '%_\\');

                $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$keyword}%"));
            })
            ->when($filters['type'] ?? null, fn($query, $v) => $query->where('type', $v))
            ->when($filters['date_from'] ?? null, fn($query, $v) => $query->whereDate('date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn($query, $v) => $query->whereDate('date', '<=', $v))
            ->latest('date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $typeLabels = AttendanceRequest::typeLabels();

        return view('attendance-approvals.manage', compact('attendanceRequests', 'filters', 'typeLabels'));
    }

    /**
     * Hapus pengajuan kehadiran yang sudah final approved.
     */
    public function destroy(DeleteApprovedAttendanceRequest $request, AttendanceRequest $attendanceRequest): RedirectResponse
    {
        try {
            $snapshot = $this->deletion->delete(
                $attendanceRequest,
                $request->user(),
                $request->validated('alasan'),
            );
        } catch (ModelNotFoundException) {
            return back()->with('error', 'Pengajuan kehadiran tidak ditemukan, kemungkinan sudah dihapus.');
        }

        $this->notifyEmployee($snapshot);

        return back()->with(
            'success',
            "{$snapshot['type']} milik " . ucwords($snapshot['employee_name']) . ' tanggal '
                . Carbon::parse($snapshot['date'])->format('d/m/Y') . ' berhasil dihapus.'
        );
    }

    /**
     * Notifikasi ke pemohon, dikirim SETELAH transaksi selesai.
     */
    private function notifyEmployee(array $snapshot): void
    {
        try {
            User::find($snapshot['employee_id'])?->notify(new AttendanceRequestDeleted($snapshot));
        } catch (\Throwable $e) {
            Log::error('[AttendanceDeletion] Gagal mengirim notifikasi.', [
                'attendance_request_id' => $snapshot['attendance_request_id'],
                'recipient'             => $snapshot['employee_id'],
                'error'                 => $e->getMessage(),
            ]);
        }
    }
}
