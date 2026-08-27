<?php

namespace App\Services;

use App\Models\LeaveType;
use App\Models\PublicHoliday;
use App\Models\QuotaSetting;
use App\Models\User;
use App\Models\UserLeaveBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveQuotaService
{
    /**
     * Generate atau sinkronkan kuota cuti untuk satu user pada tahun tertentu.
     *
     * Aturan:
     * - Jika record belum ada → buat baru dengan kuota penuh.
     * - Jika record sudah ada → hanya perbarui total_quota dan recalculate
     *   remaining (remaining = total_quota - used). Nilai used TIDAK disentuh.
     * - Skip super_admin, skip jenis cuti yang tidak sesuai gender/masa kerja.
     */
    public function generateForUser(User $user, int $year): array
    {
        if ($user->role === 'super_admin') {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $leaveTypes      = LeaveType::where('is_active', true)->get();
        $jointLeaveCount = PublicHoliday::countJointLeaveForYear($year);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($leaveTypes as $leaveType) {
            // Skip jenis cuti khusus gender yang tidak cocok dengan user
            if ($leaveType->gender && $leaveType->gender !== $user->gender) {
                $skipped++;
                continue;
            }

            // Skip jika masa kerja belum memenuhi syarat minimum
            if ($user->masaKerjaTahun() < $leaveType->min_years) {
                $skipped++;
                continue;
            }

            // Hitung kuota efektif (cuti tahunan dikurangi cuti bersama)
            $quota = $leaveType->quota;
            if ($leaveType->is_annual_leave) {
                $quota = max(0, $leaveType->quota - $jointLeaveCount);
            }

            $existing = UserLeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->first();

            if ($existing) {
                // Record sudah ada: perbarui total_quota dan recalculate remaining.
                // JANGAN sentuh used — data pemakaian cuti yang sudah terjadi tidak boleh dihapus.
                $newRemaining = max(0, $quota - $existing->used);
                $existing->update([
                    'total_quota' => $quota,
                    'remaining'   => $newRemaining,
                ]);
                $updated++;
            } else {
                // Record belum ada: buat baru
                UserLeaveBalance::create([
                    'user_id'       => $user->id,
                    'leave_type_id' => $leaveType->id,
                    'year'          => $year,
                    'total_quota'   => $quota,
                    'used'          => 0,
                    'remaining'     => $quota,
                ]);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * Generate atau sinkronkan kuota untuk semua user approved (batch).
     * Dipakai oleh HRD lewat QuotaController dan oleh Artisan command.
     */
    public function generateForAllUsers(int $year): array
    {
        $totalCreated = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;

        DB::transaction(function () use ($year, &$totalCreated, &$totalUpdated, &$totalSkipped) {
            User::where('role', '!=', 'super_admin')
                ->where('status', 'approved')
                ->chunk(100, function ($users) use ($year, &$totalCreated, &$totalUpdated, &$totalSkipped) {
                    foreach ($users as $user) {
                        $result = $this->generateForUser($user, $year);
                        $totalCreated += $result['created'];
                        $totalUpdated += $result['updated'];
                        $totalSkipped += $result['skipped'];
                    }
                });
        });

        $jointLeaveCount = PublicHoliday::countJointLeaveForYear($year);
        Log::info("LeaveQuotaService: generate tahun {$year} selesai — "
            . "dibuat: {$totalCreated}, diperbarui: {$totalUpdated}, "
            . "dilewati: {$totalSkipped}. Cuti bersama: {$jointLeaveCount} hari.");

        return [
            'created' => $totalCreated,
            'updated' => $totalUpdated,
            'skipped' => $totalSkipped,
        ];
    }
}
