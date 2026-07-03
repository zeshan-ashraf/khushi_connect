<?php

namespace App\Http\Middleware;

use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EasypaisaPendingRequestsMiddleware
{
    private const CACHE_KEY_COUNT = 'easypaisa_pending_requests_count';

    private const CACHE_KEY_BLOCKED = 'easypaisa_pending_requests_blocked';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->payment_method !== 'easypaisa') {
            return $next($request);
        }

        $blockThreshold = (int) config('easypaisa.pending_block_threshold', 500);
        $resumeThreshold = (int) config('easypaisa.pending_resume_threshold', 100);
        $cacheTtlMinutes = (int) config('easypaisa.pending_count_cache_minutes', 1);

        $isBlocked = (bool) Cache::get(self::CACHE_KEY_BLOCKED, false);
        $pendingCount = $this->resolvePendingCount($isBlocked, $cacheTtlMinutes);

        if ($isBlocked) {
            if ($pendingCount <= $resumeThreshold) {
                Cache::forget(self::CACHE_KEY_BLOCKED);
                Cache::forget(self::CACHE_KEY_COUNT);

                return $next($request);
            }

            return $this->rejectResponse($pendingCount, $blockThreshold, $resumeThreshold, 'blocked');
        }

        if ($pendingCount >= $blockThreshold) {
            Cache::put(self::CACHE_KEY_BLOCKED, true, now()->addDay());
            Cache::forget(self::CACHE_KEY_COUNT);

            return $this->rejectResponse($pendingCount, $blockThreshold, $resumeThreshold, 'threshold_reached');
        }

        return $next($request);
    }

    private function resolvePendingCount(bool $fresh, int $cacheTtlMinutes): int
    {
        if ($fresh) {
            return $this->countPendingEasypaisaTransactions();
        }

        return Cache::remember(
            self::CACHE_KEY_COUNT,
            now()->addMinutes($cacheTtlMinutes),
            fn (): int => $this->countPendingEasypaisaTransactions(),
        );
    }

    private function countPendingEasypaisaTransactions(): int
    {
        return Transaction::where('status', 'pending')
            ->where('txn_type', 'easypaisa')
            ->count();
    }

    private function rejectResponse(
        int $pendingCount,
        int $blockThreshold,
        int $resumeThreshold,
        string $reason,
    ): Response {
        Log::channel('payin')->warning('Easypaisa pending queue limit reached', [
            'pending_requests' => $pendingCount,
            'block_threshold' => $blockThreshold,
            'resume_threshold' => $resumeThreshold,
            'reason' => $reason,
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'System is processing pending Easypaisa requests. Please wait a few minutes and try again.',
        ], 429);
    }
}
