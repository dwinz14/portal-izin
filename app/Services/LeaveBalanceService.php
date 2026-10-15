<?php

namespace App\Services;

use App\Exceptions\LeaveRuleViolation;
use App\Models\Leave;
use App\Models\UserLeaveBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Satu-satunya tempat yang memutasi saldo kuota (user_leave_balances) akibat cuti.
 */
class LeaveBalanceService
{
    /**
     *
     * @return int|null Sisa kuota SETELAH pemotongan; null bila jenis cuti tidak berkuota.
     */
    public function deduct(Leave $leave): ?int
    {
        $leaveType = $leave->leaveType;

        if (! $leaveType || $leaveType->quota <= 0) {
            return null;
        }

        return DB::transaction(function () use ($leave, $leaveType) {
            // Baca ulang & kunci baris cuti: jaga dari pemotongan ganda jika terpanggil dua kali.
            $deductedYear = Leave::query()
                ->whereKey($leave->id)
                ->lockForUpdate()
                ->value('quota_year');

            if ($deductedYear !== null) {
                Log::warning('LeaveBalanceService::deduct dilewati: saldo cuti sudah pernah dipotong.', [
                    'leave_id'   => $leave->id,
                    'quota_year' => $deductedYear,
                ]);

                $leave->quota_year = (int) $deductedYear;

                return UserLeaveBalance::where('user_id', $leave->user_id)
                    ->where('leave_type_id', $leave->leave_type_id)
                    ->where('year', $deductedYear)
                    ->value('remaining');
            }

            $days = (int) $leave->total_hari;
            $year = now()->year;

            $balance = UserLeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $remainingAfter = max(0, $balance->remaining - $days);

                $balance->update([
                    'used'      => DB::raw("used + {$days}"),
                    'remaining' => DB::raw("remaining - {$days}"),
                ]);
            } else {
                $remainingAfter = max(0, $leaveType->quota - $days);

                UserLeaveBalance::create([
                    'user_id'       => $leave->user_id,
                    'leave_type_id' => $leave->leave_type_id,
                    'year'          => $year,
                    'total_quota'   => $leaveType->quota,
                    'used'          => $days,
                    'remaining'     => $remainingAfter,
                ]);
            }

            $leave->forceFill(['quota_year' => $year])->save();

            return $remainingAfter;
        });
    }

    /**
     * Kembalikan seluruh hari cuti ke saldo (dipakai saat cuti approved dibatalkan).
     * Harus dipanggil SEBELUM baris cuti dihapus.
     *
     * @return array|null Detail perubahan saldo; null bila tidak ada pemotongan yang tercatat
     *                    (jenis cuti tanpa kuota, atau data lama tanpa quota_year).
     */
    public function restore(Leave $leave): ?array
    {
        if ($leave->quota_year === null) {
            return null;
        }

        return $this->shiftUsed($leave, -(int) $leave->total_hari);
    }

    /**
     * Sesuaikan saldo dengan SELISIH hari baru terhadap hari yang sudah dipotong
     * (dipakai saat tanggal cuti approved diubah). Harus dipanggil SEBELUM total_hari cuti diganti.
     *
     * @return array|null Detail perubahan saldo; null bila tidak ada pemotongan yang tercatat.
     */
    public function adjust(Leave $leave, int $newDays): ?array
    {
        if ($leave->quota_year === null) {
            return null;
        }

        return $this->shiftUsed($leave, $newDays - (int) $leave->total_hari);
    }

    private function shiftUsed(Leave $leave, int $deltaUsed): array
    {
        return DB::transaction(function () use ($leave, $deltaUsed) {
            $year = (int) $leave->quota_year;

            $balance = UserLeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                if ($deltaUsed > 0) {
                    throw new LeaveRuleViolation("Saldo kuota tahun {$year} milik pemohon tidak ditemukan, sehingga penambahan hari cuti tidak dapat diproses.");
                }

                Log::warning('LeaveBalanceService: saldo tidak ditemukan saat mengembalikan kuota.', [
                    'leave_id'   => $leave->id,
                    'quota_year' => $year,
                ]);

                return [
                    'year'            => $year,
                    'applied'         => false,
                    'requested_delta' => $deltaUsed,
                    'delta_used'      => 0,
                    'note'            => 'Saldo kuota tidak ditemukan; tidak ada kuota yang dikembalikan.',
                ];
            }

            // Pemakaian tidak boleh negatif
            $applied = max($deltaUsed, -(int) $balance->used);

            if ($applied > 0 && $balance->remaining < $applied) {
                throw new LeaveRuleViolation('Sisa kuota tidak mencukupi untuk menambah hari cuti.');
            }

            $result = [
                'year'             => $year,
                'applied'          => true,
                'requested_delta'  => $deltaUsed,
                'delta_used'       => $applied,
                'used_before'      => (int) $balance->used,
                'used_after'       => (int) $balance->used + $applied,
                'remaining_before' => (int) $balance->remaining,
                'remaining_after'  => (int) $balance->remaining - $applied,
            ];

            if ($applied !== 0) {
                $balance->update([
                    'used'      => $result['used_after'],
                    'remaining' => $result['remaining_after'],
                ]);
            }

            return $result;
        });
    }
}
