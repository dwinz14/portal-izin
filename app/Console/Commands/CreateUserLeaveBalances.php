<?php

namespace App\Console\Commands;

use App\Services\LeaveQuotaService;
use Illuminate\Console\Command;

class CreateUserLeaveBalances extends Command
{
    protected $signature = 'app:create-user-leave-balances
                            {--year= : Tahun untuk generate kuota (default: tahun berjalan)}';

    protected $description = 'Generate atau sinkronkan kuota cuti semua user untuk tahun tertentu';

    public function handle(LeaveQuotaService $quotaService): int
    {
        $year = (int) ($this->option('year') ?: now()->year);

        $this->info("Memulai generate kuota cuti untuk tahun {$year}...");

        $result = $quotaService->generateForAllUsers($year);

        $this->info("Selesai!");
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['Dibuat (baru)',   $result['created']],
                ['Diperbarui',      $result['updated']],
                ['Dilewati',        $result['skipped']],
            ]
        );

        return Command::SUCCESS;
    }
}
