<?php

namespace App\Services;

use App\Models\AttendanceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Penghapusan pengajuan kehadiran yang SUDAH final approved, oleh atasan yang menyetujuinya.
 */
class AttendanceDeletionService
{
    /**
     * @return array<string, mixed>  Snapshot data biasa (bukan model) untuk notifikasi.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(AttendanceRequest $attendanceRequest, User $actor, string $alasan): array
    {
        return DB::transaction(function () use ($attendanceRequest, $actor, $alasan) {
            // Kunci & baca ulang kondisi terbaru: status / approver bisa berubah sejak form
            // dibuka, atau terjadi klik ganda.
            $locked = AttendanceRequest::with('user')
                ->lockForUpdate()
                ->findOrFail($attendanceRequest->id);

            Gate::forUser($actor)->authorize('deleteApproved', $locked);

            $snapshot = $this->snapshot($locked, $actor, $alasan);

            ActivityLogger::logAs(
                $actor,
                'attendance.deleted_by_approver',
                'Menghapus ' . $this->jenis($snapshot) . ' milik ' . ucwords($snapshot['employee_name']) . ' pada ' .
                    Carbon::parse($snapshot['date'])->format('d/m/Y') . ' yang sudah disetujui',
                $locked,
                $this->logMetadata($snapshot, $locked)
            );

            // Hapus terakhir: foto bukti dihapus oleh event model.
            $locked->delete();

            return $snapshot;
        });
    }

    /** Data biasa yang memuat semua informasi pengajuan saat dihapus. */
    private function snapshot(AttendanceRequest $request, User $actor, string $alasan): array
    {
        $date = Carbon::parse($request->date);

        return [
            'attendance_request_id' => $request->id,
            'employee_id'           => $request->user_id,
            'employee_name'         => $request->user?->name ?? '-',
            'employee_nik'          => $request->user?->nik,
            'actor_id'              => $actor->id,
            'actor_name'            => $actor->name,
            'type'                  => $request->type_label,
            'update_type'           => $request->type === AttendanceRequest::TYPE_UPDATE_ATTENDANCE
                ? $request->update_type_label
                : null,
            'date'                  => $date->toDateString(),
            'start_time'            => $request->start_time ? Carbon::parse($request->start_time)->format('H:i') : null,
            'end_time'              => $request->end_time ? Carbon::parse($request->end_time)->format('H:i') : null,
            'reason'                => $request->reason,
            'approved_at'           => $request->approved_at?->toDateTimeString(),
            'alasan'                => $alasan,
            'is_retroactive'        => $date->lt(Carbon::today()), // dihapus setelah tanggal kehadiran lewat
            'had_proof'             => $request->proof_image !== null,
        ];
    }

    /** "Update Absensi (Check-in Saja)" / "Datang Terlambat". */
    private function jenis(array $snapshot): string
    {
        return $snapshot['type'] . ($snapshot['update_type'] ? " ({$snapshot['update_type']})" : '');
    }

    /** Metadata datar (nilai skalar) agar tampil sebagai chip di Aktivitas Pengguna. */
    private function logMetadata(array $snapshot, AttendanceRequest $request): array
    {
        $jam = collect([$snapshot['start_time'], $snapshot['end_time']])->filter()->implode(' - ');

        return array_filter([
            'pemohon'               => $snapshot['employee_name'],
            'nik'                   => $snapshot['employee_nik'],
            'jenis'                 => $this->jenis($snapshot),
            'tanggal'               => Carbon::parse($snapshot['date'])->format('d/m/Y'),
            'jam'                   => $jam !== '' ? $jam : null,
            'alasan_pengajuan'      => $snapshot['reason'],
            'disetujui_pada'        => $request->approved_at?->format('d/m/Y H:i'),
            'alasan'                => $snapshot['alasan'],
            'bukti_foto'            => $snapshot['had_proof'] ? 'ada (ikut terhapus)' : null,
            'retroaktif'            => $snapshot['is_retroactive'] ? 'ya' : null,
            'attendance_request_id' => $snapshot['attendance_request_id'],
        ], fn($v) => $v !== null && $v !== '');
    }
}
