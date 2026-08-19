<?php

namespace App\Helpers;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use InvalidArgumentException;

class LeaveHelper
{
    /**
     * Hitung jumlah hari kerja (Senin-Jumat) antara dua tanggal,
     * tidak termasuk libur nasional (tanggal merah) dan cuti bersama
     * yang jatuh di hari kerja. Tanggal mulai dan selesai ikut dihitung.
     *
     * Cuti bersama juga dikurangkan di sini karena jatahnya sudah
     * dipotong dari kuota cuti tahunan saat HRD generate kuota
     * (kuota = 12 - jumlah cuti bersama), jadi tidak dihitung lagi
     * sebagai hari cuti di dalam pengajuan.
     */
    public static function calculateWorkingDays(
        string|Carbon $startDate,
        string|Carbon $endDate
    ): int {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            throw new InvalidArgumentException(
                'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.'
            );
        }

        $nonWorkingDates = collect();
        for ($year = $start->year; $year <= $end->year; $year++) {
            $nonWorkingDates = $nonWorkingDates
                ->merge(PublicHoliday::getHolidayDatesForYear($year))
                ->merge(PublicHoliday::getJointLeaveDatesForYear($year));
        }
        // Lookup cepat: 'Y-m-d' => true
        $nonWorkingDates = $nonWorkingDates->unique()->flip();

        $workingDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekday() && ! isset($nonWorkingDates[$current->format('Y-m-d')])) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }
}
