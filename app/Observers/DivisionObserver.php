<?php

namespace App\Observers;

use App\Models\Division;
use Illuminate\Support\Facades\Cache;

class DivisionObserver
{
    public function saved(Division $division): void
    {
        Cache::forget('divisions_all');
    }

    public function deleted(Division $division): void
    {
        Cache::forget('divisions_all');
    }
}
