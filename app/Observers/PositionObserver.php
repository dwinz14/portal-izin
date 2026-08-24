<?php

namespace App\Observers;

use App\Models\Position;
use Illuminate\Support\Facades\Cache;

class PositionObserver
{
    public function saved(Position $position): void
    {
        Cache::forget('positions_all');
    }

    public function deleted(Position $position): void
    {
        Cache::forget('positions_all');
    }
}
