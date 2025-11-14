<?php

namespace App\Http\Middleware;

use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EasypaisaPendingRequestsMiddleware
{
    private const CACHE_KEY = 'easypaisa_pending_requests_count';
    private const CACHE_TTL_MINUTES = 5;
    private const MAX_PENDING_REQUESTS = 400;

    public function handle(Request $request, Closure $next)
    {
        if ($request->payment_method !== 'easypaisa') {
            return $next($request);
        }

        $pendingCount = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            function () {
                return Transaction::where('status', 'pending')
                    ->where('txn_type', 'easypaisa')
                    ->count();
            }
        );

        if ($pendingCount >= self::MAX_PENDING_REQUESTS) {
            Log::channel('payin')->warning('Easypaisa pending queue limit reached', [
                'pending_requests' => $pendingCount,
                'limit' => self::MAX_PENDING_REQUESTS,
                'cache_ttl_minutes' => self::CACHE_TTL_MINUTES,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'System is processing pending Easypaisa requests. Please wait a few minutes and try again.',
            ], 429);
        }

        return $next($request);
    }
}

