<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictUserTransactionRange
{
    /**
     * Per-user transaction limits (lowercase email => min/max amount).
     */
    private const USER_LIMIT_RULES = [
        'okpaysev@gmail.com' => ['min' => 300, 'max' => 50000],
        'unipay@khushiconnect.com' => ['min' => 300, 'max' => 50000],
        'flpay@khushiconnect.com' => ['min' => 300, 'max' => 50000],
        'unipay2@khushiconnect.com' => ['min' => 300, 'max' => 50000],
        '24paybase@khushiconnect.com' => ['min' => 100, 'max' => 50000],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $clientEmail = strtolower(trim((string) $request->input('client_email', '')));
        $limitRule = $this->resolveUserLimitRule($clientEmail);

        if ($limitRule === null) {
            return $next($request);
        }

        $amount = $request->input('amount');
        if (!is_numeric($amount)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount is required and must be numeric.',
            ], 422);
        }

        $amountValue = (float) $amount;
        if ($amountValue < $limitRule['min'] || $amountValue > $limitRule['max']) {
            return response()->json([
                'status' => 'error',
                'message' => "Invalid transaction amount. Allowed range: {$limitRule['min']} to {$limitRule['max']}.",
            ], 422);
        }

        return $next($request);
    }

    /**
     * Single place to resolve user limits.
     * Later we can switch this to DB lookup without changing handle().
     */
    private function resolveUserLimitRule(string $clientEmail): ?array
    {
        if ($clientEmail === '') {
            return null;
        }

        $rule = self::USER_LIMIT_RULES[$clientEmail] ?? null;
        if (!is_array($rule)) {
            return null;
        }

        $min = isset($rule['min']) ? (float) $rule['min'] : null;
        $max = isset($rule['max']) ? (float) $rule['max'] : null;
        if ($min === null || $max === null || $min <= 0 || $max <= 0 || $min > $max) {
            return null;
        }

        return ['min' => $min, 'max' => $max];
    }
}
