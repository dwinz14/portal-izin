<?php

namespace App\Helpers;

use App\Enums\WorkSchedule;
use App\Models\PublicHoliday;
use Carbon\Carbon;
use InvalidArgumentException;

class LeaveHelper
{
    public static function calculateWorkingDays(
        string|Carbon $startDate,
        string|Carbon $endDate,
        ?WorkSchedule $schedule = null
    ): int {
        $schedule ??= WorkSchedule::Weekday;

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            throw new InvalidArgumentException(
                'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.'
            );
        }

        // Pola 7 hari: semua hari kalender dihitung, tidak perlu cek weekend/libur.
        if (! $schedule->usesBusinessCalendar()) {
            return (int) $start->diffInDays($end) + 1;
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
