<?php

namespace App\Http\Controllers;

use App\Exports\RekapKehadiranExport;
use App\Models\AttendanceRequest;
use App\Models\Office;
use App\Models\Position;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'status'      => ['nullable', 'in:pending,approved,rejected'],
            'type'        => ['nullable', 'in:late_arrival,early_departure,leave_during_work,update_attendance'],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
            'office_id'   => ['nullable', 'exists:offices,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
        ]);
    }

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $attendanceRequests = AttendanceRequest::with(['user.position', 'user.office', 'approver'])
            ->when($filters['status'] ?? null,      fn($q, $v) => $q->where('status', $v))
            ->when($filters['type'] ?? null,        fn($q, $v) => $q->where('type', $v))
            ->when($filters['date_from'] ?? null,   fn($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($filters['date_to'] ?? null,     fn($q, $v) => $q->whereDate('date', '<=', $v))
            ->when($filters['office_id'] ?? null,   fn($q, $v) => $q->whereHas('user', fn($u) => $u->where('office_id', $v)))
            ->when($filters['position_id'] ?? null, fn($q, $v) => $q->whereHas('user', fn($u) => $u->where('position_id', $v)))
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        $typeLabels = AttendanceRequest::typeLabels();
        $offices    = Office::orderBy('nama_kantor')->get();
        $positions  = Position::orderBy('nama_jabatan')->get();

        return view('hrd.attendance', compact(
            'attendanceRequests',
            'typeLabels',
            'filters',
            'offices',
            'positions',
        ));
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $filename = 'rekap_kehadiran_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new RekapKehadiranExport($filters), $filename);
    }
}
