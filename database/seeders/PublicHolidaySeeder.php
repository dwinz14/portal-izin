<?php

namespace Database\Seeders;

use App\Models\PublicHoliday;
use Illuminate\Database\Seeder;

class PublicHolidaySeeder extends Seeder
{
    /**
     * Seed data hari libur nasional & cuti bersama (SKB 3 Menteri).
     *
     * Cuti bersama 2026 (7 hari, semuanya jatuh di hari kerja):
     * 18 Mar (Nyepi), 20/23/24 Mar (Idul Fitri), 15 Mei (Kenaikan),
     * 28 Mei (Idul Adha), 24 Des (Natal) → kuota cuti tahunan 2026 = 12 - 7 = 5.
     *
     * Data 2027 hanya libur nasional (SKB 2027 belum terbit saat seeder dibuat).
     */
    public function run(): void
    {
        $holidays = [
            // ── 2026 ─────────────────────────────────────────────────────────
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi', 'type' => 'national_holiday'],
            ['date' => '2026-01-16', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'type' => 'national_holiday'],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577 Kongzili', 'type' => 'national_holiday'],
            ['date' => '2026-03-18', 'name' => 'Cuti Bersama Hari Suci Nyepi Tahun Baru Saka 1948', 'type' => 'joint_leave'],
            ['date' => '2026-03-19', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1948', 'type' => 'national_holiday'],
            ['date' => '2026-03-20', 'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'joint_leave'],
            ['date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'national_holiday'],
            ['date' => '2026-03-22', 'name' => 'Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'national_holiday'],
            ['date' => '2026-03-23', 'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'joint_leave'],
            ['date' => '2026-03-24', 'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'joint_leave'],
            ['date' => '2026-04-03', 'name' => 'Wafat Yesus Kristus / Jumat Agung', 'type' => 'national_holiday'],
            ['date' => '2026-04-05', 'name' => 'Kebangkitan Yesus Kristus (Paskah)', 'type' => 'national_holiday'],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national_holiday'],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Yesus Kristus', 'type' => 'national_holiday'],
            ['date' => '2026-05-15', 'name' => 'Cuti Bersama Kenaikan Yesus Kristus', 'type' => 'joint_leave'],
            ['date' => '2026-05-27', 'name' => 'Hari Raya Idul Adha 1447 Hijriyah', 'type' => 'national_holiday'],
            ['date' => '2026-05-28', 'name' => 'Cuti Bersama Hari Raya Idul Adha 1447 Hijriyah', 'type' => 'joint_leave'],
            ['date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570 BE', 'type' => 'national_holiday'],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national_holiday'],
            ['date' => '2026-06-16', 'name' => 'Tahun Baru Islam 1448 Hijriyah', 'type' => 'national_holiday'],
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan Republik Indonesia', 'type' => 'national_holiday'],
            ['date' => '2026-08-25', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national_holiday'],
            ['date' => '2026-12-24', 'name' => 'Cuti Bersama Hari Raya Natal', 'type' => 'joint_leave'],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national_holiday'],

            // ── 2027 (libur nasional; cuti bersama menunggu SKB 2027) ────────
            ['date' => '2027-01-01', 'name' => 'Tahun Baru 2027 Masehi', 'type' => 'national_holiday'],
            ['date' => '2027-01-05', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'type' => 'national_holiday'],
            ['date' => '2027-02-06', 'name' => 'Tahun Baru Imlek 2578 Kongzili', 'type' => 'national_holiday'],
            ['date' => '2027-03-09', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1949', 'type' => 'national_holiday'],
            ['date' => '2027-03-10', 'name' => 'Hari Raya Idul Fitri 1448 Hijriyah', 'type' => 'national_holiday'],
            ['date' => '2027-03-26', 'name' => 'Wafat Yesus Kristus / Jumat Agung', 'type' => 'national_holiday'],
            ['date' => '2027-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national_holiday'],
            ['date' => '2027-05-06', 'name' => 'Kenaikan Yesus Kristus', 'type' => 'national_holiday'],
            ['date' => '2027-05-17', 'name' => 'Hari Raya Idul Adha 1448 Hijriyah', 'type' => 'national_holiday'],
            ['date' => '2027-05-20', 'name' => 'Hari Raya Waisak 2571 BE', 'type' => 'national_holiday'],
            ['date' => '2027-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national_holiday'],
            ['date' => '2027-06-06', 'name' => 'Tahun Baru Islam 1449 Hijriyah', 'type' => 'national_holiday'],
            ['date' => '2027-08-15', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national_holiday'],
            ['date' => '2027-08-17', 'name' => 'Hari Kemerdekaan Republik Indonesia', 'type' => 'national_holiday'],
            ['date' => '2027-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national_holiday'],
            ['date' => '2027-12-26', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'type' => 'national_holiday'],
        ];

        foreach ($holidays as $holiday) {
            PublicHoliday::updateOrCreate(
                ['date' => $holiday['date']],
                [
                    'name' => $holiday['name'],
                    'type' => $holiday['type'],
                    'is_active' => true,
                ]
            );
        }

        PublicHoliday::clearCacheForYear(2026);
        PublicHoliday::clearCacheForYear(2027);
    }
}
