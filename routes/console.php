<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Sinkronisasi data hari libur dari API → draft (direview HR sebelum diaktifkan).
// Jalan tiap tanggal 1 & 15 bulan; draft tidak mengubah data aktif.
Schedule::command('holidays:sync')->cron('0 2 1,15 * *');
