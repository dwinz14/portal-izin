<?php

namespace App\Console\Commands;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync {--year= : Tahun yang akan disinkronkan (default: tahun berjalan)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data hari libur nasional & cuti bersama dari API sebagai draft (is_active=false) untuk direview HR';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = (int) ($this->option('year') ?: now()->year);
        $url = config('services.holiday_api.url', 'https://api-hari-libur.vercel.app/api');

        $this->info("Mengambil data hari libur tahun {$year} dari: {$url}");

        try {
            $response = Http::timeout(15)->get($url, ['year' => $year]);
        } catch (\Throwable $e) {
            $this->error('Gagal terhubung ke API: '.$e->getMessage());
            Log::warning('SyncHolidays: gagal terhubung ke API.', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }

        if ($response->failed()) {
            $this->error('API merespons dengan status '.$response->status());

            return self::FAILURE;
        }

        $payload = $response->json();
        $items = $payload['data'] ?? $payload;

        if (! is_array($items) || count($items) === 0) {
            $this->warn('Tidak ada data hari libur dari API untuk tahun '.$year);

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $date = Carbon::parse($item['date'] ?? null);
            $name = trim($item['description'] ?? $item['name'] ?? '');

            if (! $date || $name === '') {
                $skipped++;

                continue;
            }

            // Klasifikasi: "Cuti Bersama" = joint_leave, sisanya libur nasional
            $type = str_contains(strtolower($name), 'cuti bersama')
                ? 'joint_leave'
                : 'national_holiday';

            $exists = PublicHoliday::where('date', $date->format('Y-m-d'))->exists();

            if ($exists) {
                // Jangan timpa data yang sudah ada (termasuk hasil review/editan HR)
                $skipped++;

                continue;
            }

            // Simpan sebagai draft (is_active=false) agar HR review dulu
            PublicHoliday::create([
                'date' => $date->format('Y-m-d'),
                'name' => $name,
                'type' => $type,
                'year' => $year,
                'description' => 'Hasil sinkronisasi API (draft, belum aktif).',
                'is_active' => false,
            ]);

            $created++;
        }

        PublicHoliday::clearCacheForYear($year);

        $this->info("Selesai: {$created} draft baru, {$skipped} dilewati (sudah ada).");
        $this->warn('Draft belum aktif. Aktifkan melalui menu HRD > Hari Libur setelah direview.');

        return self::SUCCESS;
    }
}
