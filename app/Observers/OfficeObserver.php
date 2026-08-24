<?php

namespace App\Observers;

use App\Models\Office;
use Illuminate\Support\Facades\Cache;

class OfficeObserver
{
    public function saved(Office $office): void
    {
        Cache::forget('offices_all');
    }

    public function deleted(Office $office): void
    {
        Cache::forget('offices_all');
    }
}
