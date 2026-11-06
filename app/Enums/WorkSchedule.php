<?php

namespace App\Enums;

enum WorkSchedule: string
{
    case Weekday = 'weekday';
    case AllDays = 'all_days';

    /** True jika Sabtu/Minggu, libur nasional, dan cuti bersama tidak dihitung sebagai hari cuti. */
    public function usesBusinessCalendar(): bool
    {
        return $this === self::Weekday;
    }

    public function label(): string
    {
        return match ($this) {
            self::Weekday => 'Senin–Jumat (5 hari kerja)',
            self::AllDays => 'Setiap hari (7 hari, shift)',
        };
    }

    /** Untuk dropdown di form admin. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $c) => [$c->value => $c->label()])
            ->all();
    }
}
