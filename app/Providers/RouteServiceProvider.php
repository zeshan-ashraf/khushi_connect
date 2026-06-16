<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/login';
    public const USER = '/user/dashboard';
    public const ADMIN = '/admin/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // Payout checkout uses the dedicated "payout" limiter instead.
            if ($request->is('api/payout/checkout', 'api/v1/payout/checkout')) {
                return Limit::none();
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('payout', function (Request $request) {
            $ip = $request->ip();
            $vipIps = config('payout.rate_limit.vip_ips', []);
            $defaultLimit = (int) config('payout.rate_limit.default_per_minute', 60);
            $vipLimit = (int) config('payout.rate_limit.vip_per_minute', 200);

            if (in_array($ip, $vipIps, true)) {
                return Limit::perMinute($vipLimit)->by('payout-vip:'.$ip);
            }

            return Limit::perMinute($defaultLimit)->by('payout:'.$ip);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
            
            Route::middleware('web')
            ->group(base_path('routes/admin.php'));
        });
    }
}
