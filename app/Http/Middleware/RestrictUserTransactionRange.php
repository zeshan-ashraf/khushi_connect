<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\PhoneVerificationService;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class RestrictUserTransactionRange
{
    public function __construct(
        private readonly PhoneVerificationService $phoneVerificationService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->resolveClientUser($request);
        if ($user === null) {
            return $next($request);
        }

        if (!$user->transaction_limit_enabled) {
            return $next($request);
        }

        if (!$this->shouldApplyConfiguredLimits($user, $request)) {
            return $next($request);
        }

        $min = (int) $user->transaction_amount_min;
        $max = (int) $user->transaction_amount_max;
        if ($min < 1 || $max > 50000 || $min >= $max) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction limits are misconfigured for this merchant. Please contact support.',
            ], 422);
        }

        $amount = $request->input('amount');
        if (!is_numeric($amount)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount is required and must be numeric.',
            ], 422);
        }

        $amountValue = (float) $amount;
        if ($amountValue < $min || $amountValue > $max) {
            return response()->json([
                'status' => 'error',
                'message' => "Invalid transaction amount. Allowed range: {$min} to {$max}.",
            ], 422);
        }

        return $next($request);
    }

    private function resolveClientUser(Request $request): ?User
    {
        $user = $request->user_model ?? $request->user;
        if (!$user instanceof User && $request->filled('client_email')) {
            $email = strtolower(trim((string) $request->input('client_email')));
            if ($email !== '') {
                $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            }
        }

        if (!$user instanceof User || $user->user_role !== 'client') {
            return null;
        }

        return $user;
    }

    /**
     * Per-user limits apply for verified EasyPaisa users when new-user verification is on;
     * unverified EasyPaisa payers stay under new_user_max_amount only (handled earlier in phone.verified).
     */
    private function shouldApplyConfiguredLimits(User $user, Request $request): bool
    {
        if (!$user->new_user_verification) {
            return true;
        }

        if (strtolower((string) $request->input('payment_method', '')) !== 'easypaisa') {
            return true;
        }

        $inputKey = (string) config('phone_verification.phone_input_key', 'phone');
        $rawPhone = (string) $request->input($inputKey, '');

        try {
            $normalizedPhone = $this->phoneVerificationService->normalizePhone($rawPhone);
        } catch (InvalidArgumentException) {
            return false;
        }

        return $this->phoneVerificationService->isVerified($normalizedPhone);
    }
}
