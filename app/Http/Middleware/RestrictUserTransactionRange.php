<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictUserTransactionRange
{
    /**
     * Add target client emails (lowercase) here.
     */
    private const RESTRICTED_EMAILS = [
        // 'client1@example.com',
        // 'client2@example.com',
        'okpaysev@gmail.com',
        'unipay@khushiconnect.com',
        'flpay@khushiconnect.com',
        'unipay2@khushiconnect.com',
        '24paybase@khushiconnect.com',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $clientEmail = strtolower(trim((string) $request->input('client_email', '')));

        if ($clientEmail === '' || !in_array($clientEmail, self::RESTRICTED_EMAILS, true)) {
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
        if ($amountValue < 300 || $amountValue > 50000) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid transaction amount.',
            ], 422);
        }

        return $next($request);
    }
}
