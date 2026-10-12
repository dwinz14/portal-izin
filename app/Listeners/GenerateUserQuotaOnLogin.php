<?php

namespace App\Listeners;

use App\Models\QuotaSetting;
use App\Models\UserLeaveBalance;
use App\Services\LeaveQuotaService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class GenerateUserQuotaOnLogin
{
    public function __construct(private LeaveQuotaService $quotaService) {}

    public function handle(Login $event): void
    {
        $user = $event->user;
        $year = now()->year;

        // Skip super_admin
        if ($user->role === 'super_admin') {
            return;
        }

        // Skip user yang belum approved
        if ($user->status !== 'approved') {
            return;
        }

        // Skip jika masa kerja belum genap 1 tahun
        // (user baru yang belum setahun sudah di-generate saat register/dibuat admin)
        if (! $user->tanggal_aktif_kerja) {
            return;
        }

        // Cek apakah sudah ada minimal satu record kuota tahun ini
        // Query EXISTS — sangat ringan, tidak load semua data
        $sudahAdaKuota = UserLeaveBalance::where('user_id', $user->id)
            ->where('year', $year)
            ->exists();

        if ($sudahAdaKuota) {
            return; // Sudah ada, tidak perlu generate
        }

        // Belum ada kuota tahun ini → generate
        try {
            $result = $this->quotaService->generateForUser($user, $year);

            Log::info("Auto-generate kuota login: user [{$user->id}] {$user->name} "
                . "tahun {$year} — dibuat: {$result['created']}, "
                . "diperbarui: {$result['updated']}, dilewati: {$result['skipped']}.");
        } catch (\Throwable $e) {
            // Jangan sampai gagal generate merusak proses login
            Log::error("Auto-generate kuota login gagal: user [{$user->id}] — " . $e->getMessage());
        }
    }
}
