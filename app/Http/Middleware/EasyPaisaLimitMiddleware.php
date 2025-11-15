<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\{Transaction, ArcheiveTransaction, BackupTransaction, User};

class EasyPaisaLimitMiddleware
{
    /**
     * Monthly limit for EasyPaisa payments (460M)
     *
     * @var int
     */
    private $monthlyLimit = 480000000;

    /**
     * Client IDs that should be checked for the monthly limit
     *
     * @var array
     */
    private $restrictedClientIds = [
        4, // Add your specific client IDs here
        // Add more client IDs as needed
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check for EasyPaisa payments
        if ($request->payment_method !== 'easypaisa') {
            return $next($request);
        }

        // Get email from request and find user
        $clientEmail = $request->client_email   ;
        
        if (!$clientEmail) {
            Log::channel('payin')->error('EasyPaisaLimitMiddleware: Email not found in request', [
                'payment_method' => $request->payment_method,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Email is required.',
            ], 400);
        }
        
        // Find user by email
        $user = User::where('email', $clientEmail)->first();
        
        if (!$user) {
            Log::channel('payin')->error('EasyPaisaLimitMiddleware: User not found with email', [
                'client_email' => $clientEmail,
                'payment_method' => $request->payment_method,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        // Check if current user is in the restricted list
        if (!in_array($user->id, $this->restrictedClientIds)) {
            return $next($request);
        }

        try {
            // Get current month start and end dates
            $currentMonthStart = Carbon::now()->startOfMonth();
            $currentMonthEnd = Carbon::now()->endOfMonth();

            Log::channel('payin')->info('EasyPaisaLimitMiddleware: Checking monthly limit for client', [
                'user_id' => $user->id,
                'client_email' => $user->email,
                'month_start' => $currentMonthStart->toDateString(),
                'month_end' => $currentMonthEnd->toDateString()
            ]);

            // Calculate total EasyPaisa payin for current month from all tables
            $totalPayin = $this->calculateMonthlyEasyPaisaPayin($user->id, $currentMonthStart, $currentMonthEnd);

            Log::channel('payin')->info('EasyPaisaLimitMiddleware: Monthly payin calculation', [
                'user_id' => $user->id,
                'total_payin' => $totalPayin,
                'monthly_limit' => $this->monthlyLimit,
                'limit_exceeded' => $totalPayin >= $this->monthlyLimit
            ]);

            if ($totalPayin >= $this->monthlyLimit) {
                Log::channel('payin')->warning('EasyPaisaLimitMiddleware: Monthly limit exceeded', [
                    'user_id' => $user->id,
                    'client_email' => $user->email,
                    'total_payin' => $totalPayin,
                    'monthly_limit' => $this->monthlyLimit,
                    'excess_amount' => $totalPayin - $this->monthlyLimit,
                    'current_request_amount' => $request->amount ?? 0
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Monthly EasyPaisa limit has been reached. Please contact support.',
                    'error_code' => 'MONTHLY_LIMIT_EXCEEDED'
                ], 429); // 429 Too Many Requests
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::channel('error')->error('EasyPaisaLimitMiddleware: Error calculating monthly limit', [
                'user_id' => $user->id,
                'client_email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // In case of error, allow the request to proceed to avoid blocking legitimate transactions
            return $next($request);
        }
    }

    /**
     * Calculate total EasyPaisa payin for a user in the given month
     *
     * @param int $userId
     * @param Carbon $monthStart
     * @param Carbon $monthEnd
     * @return float
     */
    private function calculateMonthlyEasyPaisaPayin(int $userId, Carbon $monthStart, Carbon $monthEnd): float
    {
        $totalPayin = 0;
        $cacheKey = sprintf('easypaisa_monthly_total_%s', $monthStart->format('Y_m'));

        $cachedTotal = Cache::get($cacheKey);
        if ($cachedTotal !== null) {
            Log::channel('payin')->info('EasyPaisaLimitMiddleware: Using cached EasyPaisa total', [
                'cache_key' => $cacheKey,
                'grand_total' => $cachedTotal,
                'cache_ttl_minutes' => 10,
            ]);

            return (float) $cachedTotal;
        }

        try {
            // Query transactions table
            $transactionsTotal = Transaction::where('status', 'success')
                ->where('txn_type', 'easypaisa') // Assuming 'src' field indicates payment method
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');

            // Query archive_transactions table
            $archiveTotal = ArcheiveTransaction::where('status', 'success')
                ->where('txn_type', 'easypaisa')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');

            // Query backup_transactions table
            $backupTotal = BackupTransaction::where('status', 'success')
                ->where('txn_type', 'easypaisa')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $totalPayin = ($transactionsTotal ?? 0) + ($archiveTotal ?? 0) + ($backupTotal ?? 0);

            Cache::put($cacheKey, $totalPayin, now()->addMinutes(10));
            Log::channel('payin')->info('EasyPaisaLimitMiddleware: Cached EasyPaisa total', [
                'cache_key' => $cacheKey,
                'grand_total' => $totalPayin,
                'cache_ttl_minutes' => 10,
            ]);

            Log::channel('payin')->info('EasyPaisaLimitMiddleware: Detailed payin calculation', [
                'user_id' => $userId,
                'transactions_total' => $transactionsTotal ?? 0,
                'archive_total' => $archiveTotal ?? 0,
                'backup_total' => $backupTotal ?? 0,
                'grand_total' => $totalPayin,
                'month_start' => $monthStart->toDateString(),
                'month_end' => $monthEnd->toDateString()
            ]);

        } catch (\Exception $e) {
            Log::channel('error')->error('EasyPaisaLimitMiddleware: Database query error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return 0 to avoid false positives
            $totalPayin = 0;
        }

        return (float) $totalPayin;
    }
}
