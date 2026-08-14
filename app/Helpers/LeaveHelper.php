<?php

namespace App\Helpers;

use InvalidArgumentException;

use Carbon\Carbon;

class LeaveHelper
{
    /**
     * Hitung jumlah hari kerja (Senin-Jumat) antara dua tanggal.
     * Tanggal mulai dan selesai ikut dihitung.
     *
     * @param  string|Carbon  $startDate
     * @param  string|Carbon  $endDate
     * @return int
     */
    public static function calculateWorkingDays(
        string|Carbon $startDate,
        string|Carbon $endDate
    ): int {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            throw new InvalidArgumentException(
                'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.'
            );
        }

        $workingDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }
}
