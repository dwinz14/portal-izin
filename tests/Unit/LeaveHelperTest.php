<?php

namespace Tests\Unit;

use App\Helpers\LeaveHelper;
use App\Models\PublicHoliday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_working_days_excludes_national_holidays_on_weekdays(): void
    {
        // 17 Agustus 2026 = Senin (hari kemerdekaan, tanggal merah)
        PublicHoliday::create([
            'date' => '2026-08-17',
            'name' => 'Hari Kemerdekaan Republik Indonesia',
            'type' => 'national_holiday',
        ]);

        // Senin 17 s/d Jumat 21 Agustus 2026 → 5 hari kerja - 1 tanggal merah = 4
        $this->assertSame(4, LeaveHelper::calculateWorkingDays('2026-08-17', '2026-08-21'));
    }

    public function test_joint_leave_days_are_not_counted(): void
    {
        // Cuti bersama 24 Des 2026 (Kamis) tidak dihitung karena jatahnya
        // sudah dipotong dari kuota tahunan saat generate kuota
        PublicHoliday::create([
            'date' => '2026-12-24',
            'name' => 'Cuti Bersama Hari Raya Natal',
            'type' => 'joint_leave',
        ]);
        PublicHoliday::create([
            'date' => '2026-12-25',
            'name' => 'Hari Raya Natal',
            'type' => 'national_holiday',
        ]);

        // Senin 21 s/d Jumat 25 Des 2026 → 5 hari kerja - 1 cuti bersama - 1 tanggal merah = 3
        $this->assertSame(3, LeaveHelper::calculateWorkingDays('2026-12-21', '2026-12-25'));
    }

    public function test_range_covering_only_holidays_returns_zero(): void
    {
        PublicHoliday::create([
            'date' => '2026-03-23',
            'name' => 'Cuti Bersama Idul Fitri 1447 Hijriyah',
            'type' => 'joint_leave',
        ]);
        PublicHoliday::create([
            'date' => '2026-03-24',
            'name' => 'Cuti Bersama Idul Fitri 1447 Hijriyah',
            'type' => 'joint_leave',
        ]);

        // Sabtu 21 s/d Selasa 24 Maret 2026 → weekend + 2 cuti bersama → 0 hari kerja
        $this->assertSame(0, LeaveHelper::calculateWorkingDays('2026-03-21', '2026-03-24'));
    }

    public function test_range_spanning_multiple_years_uses_holidays_of_both_years(): void
    {
        PublicHoliday::create([
            'date' => '2026-12-25',
            'name' => 'Hari Raya Natal',
            'type' => 'national_holiday',
        ]);
        PublicHoliday::create([
            'date' => '2027-01-01',
            'name' => 'Tahun Baru 2027 Masehi',
            'type' => 'national_holiday',
        ]);

        // Kamis 31 Des 2026 s/d Jumat 1 Jan 2027 → 2 hari kerja - 1 tanggal merah = 1
        $this->assertSame(1, LeaveHelper::calculateWorkingDays('2026-12-31', '2027-01-01'));
    }

    public function test_weekend_days_are_not_counted(): void
    {
        // Sabtu 15 s/d Minggu 16 Agustus 2026 → 0 hari kerja
        $this->assertSame(0, LeaveHelper::calculateWorkingDays('2026-08-15', '2026-08-16'));
    }

    public function test_start_after_end_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LeaveHelper::calculateWorkingDays('2026-08-20', '2026-08-10');
    }

    public function test_count_joint_leave_only_counts_weekdays(): void
    {
        PublicHoliday::create([
            'date' => '2026-03-20',
            'name' => 'Cuti Bersama Idul Fitri',
            'type' => 'joint_leave',
        ]);
        PublicHoliday::create([
            'date' => '2026-03-21',
            'name' => 'Cuti Bersama Idul Fitri',
            'type' => 'joint_leave',
        ]);

        // 21 Maret 2026 = Sabtu → tidak dihitung, jadi total = 1
        $this->assertSame(1, PublicHoliday::countJointLeaveForYear(2026));
    }
}
