<?php

namespace App\Services;

use App\Exceptions\LeaveRuleViolation;
use App\Models\Leave;
use App\Models\LeaveAdjustment;
use App\Models\LeavePenggantiChange;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;


class LeaveAdjustmentService
{
    public function __construct(
        protected LeaveRulesService $rules,
        protected LeaveBalanceService $balances,
    ) {}

    /**
     * Batalkan (hapus permanen) cuti approved, kembalikan kuota, dan catat jejaknya.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function cancel(Leave $leave, User $actor, string $alasan): LeaveAdjustment
    {
        return DB::transaction(function () use ($leave, $actor, $alasan) {
            $locked = $this->lockAndAuthorize($leave, $actor);

            $before        = $this->describe($locked);
            $snapshot      = $this->snapshot($locked, $actor);
            $isRetroactive = $this->hasEnded($locked);

            // Harus sebelum baris cuti dihapus (butuh quota_year & total_hari).
            $quota = $this->balances->restore($locked);

            $adjustment = LeaveAdjustment::create([
                'type'           => LeaveAdjustment::TYPE_CANCELLED,
                'leave_id'       => $locked->id,
                'actor_id'       => $actor->id,
                'employee_id'    => $locked->user_id,
                'alasan'         => $alasan,
                'is_retroactive' => $isRetroactive,
                'before'         => $before,
                'after'          => null,
                'quota'          => $quota,
                'snapshot'       => $snapshot,
            ]);

            $restored = ($quota['applied'] ?? false) ? max(0, -$quota['delta_used']) : 0;

            ActivityLogger::logAs(
                $actor,
                'leave.cancelled_by_approver',
                "Membatalkan {$before['jenis_cuti']} milik {$locked->user->name} yang sudah disetujui (" .
                    $this->formatRange($before['start_date'], $before['end_date']) . ", {$before['total_hari']} hari kerja)",
                $locked,
                array_filter([
                    'pemohon'            => $locked->user->name,
                    'nik'                => $locked->user->nik,
                    'jenis_cuti'         => $before['jenis_cuti'],
                    'tanggal'            => $this->formatRange($before['start_date'], $before['end_date']),
                    'total_hari'         => $before['total_hari'],
                    'kuota_dikembalikan' => $restored,
                    'alasan'             => $alasan,
                    'retroaktif'         => $isRetroactive ? 'ya' : null,
                    'leave_id'           => $locked->id,
                    'adjustment_id'      => $adjustment->id,
                ], fn($v) => $v !== null)
            );

            // Hapus terakhir: approvals & histories ikut terhapus (cascade), foto bukti dihapus oleh event model.
            $locked->delete();

            return $adjustment;
        });
    }

    /**
     * Ubah tanggal cuti approved. Validasi jadwal/kuota sama dengan pengajuan baru
     * (LeaveRulesService), saldo disesuaikan dengan selisih hari, dan dicatat jejaknya.
     *
     * @throws LeaveRuleViolation
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function reschedule(Leave $leave, User $actor, string $newStart, string $newEnd, string $alasan): LeaveAdjustment
    {
        return DB::transaction(function () use ($leave, $actor, $newStart, $newEnd, $alasan) {
            $locked = $this->lockAndAuthorize($leave, $actor);

            $newStart = Carbon::parse($newStart)->toDateString();
            $newEnd   = Carbon::parse($newEnd)->toDateString();

            if ($newStart > $newEnd) {
                throw new LeaveRuleViolation('Tanggal selesai tidak boleh sebelum tanggal mulai.');
            }

            $oldStart = Carbon::parse($locked->start_date)->toDateString();
            $oldEnd   = Carbon::parse($locked->end_date)->toDateString();

            if ($newStart === $oldStart && $newEnd === $oldEnd) {
                throw new LeaveRuleViolation('Tanggal baru sama dengan tanggal cuti saat ini, tidak ada yang diubah.');
            }

            // Izin sakit hanya boleh untuk tanggal yang sudah/sedang berjalan (sama seperti saat pengajuan).
            if ($locked->leaveType->isSickLeave() && Carbon::parse($newEnd)->gt(Carbon::today())) {
                throw new LeaveRuleViolation('Tanggal izin sakit tidak boleh melebihi hari ini.');
            }

            // Aturan yang sama dengan pengajuan baru; cuti ini sendiri dikecualikan dari pengecekan bentrok.
            $newDays = $this->rules->assertSchedulable(
                $locked->user_id,
                $locked->leaveType,
                $newStart,
                $newEnd,
                $locked->pengganti_id ? (int) $locked->pengganti_id : null,
                $locked,
            );

            $before        = $this->describe($locked);
            $snapshot      = $this->snapshot($locked, $actor);
            $isRetroactive = $this->hasEnded($locked); // dihitung dari periode LAMA

            // Harus sebelum total_hari cuti diganti (adjust() membandingkan dengan hari lama).
            $quota = $this->balances->adjust($locked, $newDays);

            $locked->forceFill([
                'start_date' => $newStart,
                'end_date'   => $newEnd,
                'total_hari' => $newDays,
            ])->save();

            $after = $this->describe($locked);

            $adjustment = LeaveAdjustment::create([
                'type'           => LeaveAdjustment::TYPE_RESCHEDULED,
                'leave_id'       => $locked->id,
                'actor_id'       => $actor->id,
                'employee_id'    => $locked->user_id,
                'alasan'         => $alasan,
                'is_retroactive' => $isRetroactive,
                'before'         => $before,
                'after'          => $after,
                'quota'          => $quota,
                'snapshot'       => $snapshot,
            ]);

            $quotaDelta = ($quota['applied'] ?? false) ? -$quota['delta_used'] : 0; // + = kuota kembali, - = kuota terpakai

            ActivityLogger::logAs(
                $actor,
                'leave.rescheduled_by_approver',
                "Mengubah tanggal {$before['jenis_cuti']} milik {$locked->user->name} dari " .
                    $this->formatRange($before['start_date'], $before['end_date']) . ' menjadi ' .
                    $this->formatRange($after['start_date'], $after['end_date']),
                $locked,
                array_filter([
                    'pemohon'        => $locked->user->name,
                    'nik'            => $locked->user->nik,
                    'jenis_cuti'     => $before['jenis_cuti'],
                    'tanggal_lama'   => $this->formatRange($before['start_date'], $before['end_date']),
                    'tanggal_baru'   => $this->formatRange($after['start_date'], $after['end_date']),
                    'hari_lama'      => $before['total_hari'],
                    'hari_baru'      => $after['total_hari'],
                    'perubahan_kuota' => $quotaDelta !== 0 ? ($quotaDelta > 0 ? "+{$quotaDelta}" : (string) $quotaDelta) : null,
                    'alasan'         => $alasan,
                    'retroaktif'     => $isRetroactive ? 'ya' : null,
                    'leave_id'       => $locked->id,
                    'adjustment_id'  => $adjustment->id,
                ], fn($v) => $v !== null)
            );

            return $adjustment;
        });
    }

    /**
     * Kunci baris cuti, baca ulang kondisi terbaru, lalu otorisasi ulang di dalam transaksi
     * (status / approver bisa berubah sejak form dibuka, atau terjadi klik ganda).
     */
    private function lockAndAuthorize(Leave $leave, User $actor): Leave
    {
        $locked = Leave::with(['user', 'leaveType', 'pengganti', 'approvals.approver', 'approvalHistories.approver'])
            ->lockForUpdate()
            ->findOrFail($leave->id);

        Gate::forUser($actor)->authorize('adjustApproved', $locked);

        return $locked;
    }

    /** Kondisi cuti yang disimpan di kolom before/after. */
    private function describe(Leave $leave): array
    {
        return [
            'leave_type_id'  => $leave->leave_type_id,
            'jenis_cuti'     => $leave->leaveType?->name,
            'start_date'     => Carbon::parse($leave->start_date)->toDateString(),
            'end_date'       => Carbon::parse($leave->end_date)->toDateString(),
            'total_hari'     => (int) $leave->total_hari,
            'alasan_pemohon' => $leave->alasan,
            'pengganti_id'   => $leave->pengganti_id,
            'pengganti'      => $leave->pengganti?->name,
            'is_mendadak'    => (bool) $leave->is_mendadak,
            'quota_year'     => $leave->quota_year,
            'proof_image'    => $leave->proof_image, // berkas fisik ikut terhapus saat pembatalan
            'diajukan_pada'  => $leave->created_at?->toDateTimeString(),
        ];
    }

    /** Pelaku, pemilik, dan rantai approval saat itu: tetap terbaca walau akun/cuti kelak hilang. */
    private function snapshot(Leave $leave, User $actor): array
    {
        $person = fn(?User $u) => $u ? [
            'id'   => $u->id,
            'name' => $u->name,
            'nik'  => $u->nik,
            'role' => $u->role instanceof \BackedEnum ? $u->role->value : $u->role,
        ] : null;

        return [
            'actor'    => $person($actor),
            'employee' => $person($leave->user),

            'approvals' => $leave->approvals->sortBy('step')->map(fn($a) => [
                'step'        => $a->step,
                'approver_id' => $a->approver_id,
                'approver'    => $a->approver?->name,
                'status'      => $a->status,
            ])->values()->all(),

            'histories' => $leave->approvalHistories->sortBy('id')->map(fn($h) => [
                'by'      => $h->approver?->name,
                'role'    => $h->role,
                'step'    => $h->step,
                'status'  => $h->status,
                'catatan' => $h->catatan,
                'at'      => $h->created_at?->toDateTimeString(),
            ])->values()->all(),

            'pengganti_changes' => LeavePenggantiChange::with(['oldPengganti', 'newPengganti', 'changedBy'])
                ->where('leave_id', $leave->id)
                ->orderBy('id')
                ->get()
                ->map(fn($c) => [
                    'old'        => $c->oldPengganti?->name,
                    'new'        => $c->newPengganti?->name,
                    'changed_by' => $c->changedBy?->name,
                    'alasan'     => $c->alasan,
                    'at'         => $c->created_at?->toDateTimeString(),
                ])->values()->all(),
        ];
    }

    /** True bila periode cuti (saat ini tercatat) sudah berakhir: perubahan bersifat koreksi retroaktif. */
    private function hasEnded(Leave $leave): bool
    {
        return Carbon::today()->gt(Carbon::parse($leave->end_date)->startOfDay());
    }

    private function formatRange(string $start, string $end): string
    {
        return Carbon::parse($start)->format('d/m/Y') . ' s/d ' . Carbon::parse($end)->format('d/m/Y');
    }
}
