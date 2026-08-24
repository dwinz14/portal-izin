<?php

namespace App\Providers;

use App\Models\Division;
use App\Models\Office;
use App\Models\Position;
use App\Observers\DivisionObserver;
use App\Observers\OfficeObserver;
use App\Observers\PositionObserver;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function () {
            return Limit::perMinute(60)->by(request()->ip());
        });

        if (app()->environment('local')) {
            URL::forceScheme('http');
        }

        // Set timezone to WIB (Asia/Jakarta)
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');

        Carbon::setLocale('id');

        Division::observe(DivisionObserver::class);
        Position::observe(PositionObserver::class);
        Office::observe(OfficeObserver::class);
    }
}
