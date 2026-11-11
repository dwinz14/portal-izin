<?php

namespace Tests\Unit;

use App\Models\LeaveType;
use Carbon\Carbon;
use Tests\TestCase;

class LeaveTypeMendadakTest extends TestCase
{
    private function type(int $days): LeaveType
    {
        return new LeaveType(['advance_notice_days' => $days]);
    }

    public function test_start_inside_notice_window_is_mendadak(): void
    {
        $today = Carbon::parse('2026-11-11');
        $type  = $this->type(7);

        $this->assertTrue($type->isMendadakFor('2026-11-11', $today));  // hari ini
        $this->assertTrue($type->isMendadakFor('2026-11-17', $today));  // H-6
        $this->assertFalse($type->isMendadakFor('2026-11-18', $today)); // tepat H-7 = normal
        $this->assertFalse($type->isMendadakFor('2026-12-25', $today));
    }

    public function test_zero_notice_days_is_never_mendadak(): void
    {
        $today = Carbon::parse('2026-11-11');
        $type  = $this->type(0);

        $this->assertFalse($type->hasAdvanceNoticeRule());
        $this->assertNull($type->earliestNormalStartDate($today));
        $this->assertFalse($type->isMendadakFor('2026-11-10', $today)); // tanggal lampau
        $this->assertFalse($type->isMendadakFor('2026-11-11', $today)); // hari ini
        $this->assertFalse($type->isMendadakFor('2026-11-12', $today)); // besok
    }

    public function test_custom_notice_days(): void
    {
        $today = Carbon::parse('2026-11-11');
        $type  = $this->type(3);

        $this->assertTrue($type->isMendadakFor('2026-11-13', $today));
        $this->assertFalse($type->isMendadakFor('2026-11-14', $today));
        $this->assertSame('2026-11-14', $type->earliestNormalStartDate($today)->toDateString());
    }

    public function test_seven_days_matches_legacy_rule(): void
    {
        // Pengaman regresi: hasilnya identik dengan rumus lama (start < hari ini + 1 minggu).
        $today = Carbon::parse('2026-11-11');
        $type  = $this->type(7);

        foreach (range(-3, 20) as $offset) {
            $start  = $today->copy()->addDays($offset);
            $legacy = $start->lt($today->copy()->addWeek());

            $this->assertSame($legacy, $type->isMendadakFor($start, $today), "offset {$offset}");
        }
    }
}
