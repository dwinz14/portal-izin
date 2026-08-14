<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateUserLeaveBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user-leave-balances {--year= : Tahun untuk membuat saldo cuti}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat saldo cuti untuk semua pengguna berdasarkan jenis cuti yang aktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?: now()->year;

        $this->info("Membuat saldo cuti untuk tahun {$year}...");

        $users = \App\Models\User::all();
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();

        $created = 0;
        $skipped = 0;

        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                // Skip jika jenis cuti khusus gender dan user tidak sesuai
                if ($leaveType->gender && $leaveType->gender !== $user->gender) {
                    continue;
                }

                // Cek apakah sudah ada saldo untuk tahun ini
                $existing = \App\Models\UserLeaveBalance::where('user_id', $user->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $year)
                    ->exists();

                if (!$existing) {
                    \App\Models\UserLeaveBalance::create([
                        'user_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                        'total_quota' => $leaveType->quota,
                        'remaining' => $leaveType->quota,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }
        }

        $this->info("Selesai! {$created} saldo cuti dibuat, {$skipped} dilewati (sudah ada).");
    }
}
