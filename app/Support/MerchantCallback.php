<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * POST merchant callback/webhook payloads with optional HMAC signing.
 */
final class MerchantCallback
{
    /**
     * Standard payin notify payload (success, failed, pending, etc.).
     *
     * @param  Transaction|object{orderId:string,transactionId:?string,txn_ref_no:string,amount:mixed,status:string,url:string}  $transaction
     */
    public static function buildPayinPayload(object $transaction): array
    {
        return [
            'orderId' => $transaction->orderId,
            'tid' => $transaction->transactionId,
            'tRefNo' => $transaction->txn_ref_no,
            'amount' => $transaction->amount,
            'status' => $transaction->status,
        ];
    }

    /**
     * Send payin merchant callback with unified payload and logging (single POST, no retries).
     */
    public static function notifyPayin(
        object $transaction,
        ?User $user = null,
        int $timeout = 120,
        ?string $requestId = null,
        string $source = 'payin',
    ): bool {
        $requestId = $requestId ?: uniqid();
        $url = (string) $transaction->url;
        $data = self::buildPayinPayload($transaction);

        try {
            Log::channel('payout')->info('[TestPaymentService] Sending callback notification', [
                'request_id' => $requestId,
                'source' => $source,
                'url' => $url,
                'data' => $data,
            ]);

            $response = self::post($url, $data, $user, $timeout);

            if ($response->successful()) {
                Log::channel('payout')->info('[TestPaymentService] Callback successful', [
                    'request_id' => $requestId,
                    'source' => $source,
                    'status_code' => $response->status(),
                    'url' => $url,
                ]);

                return true;
            }

            Log::channel('payout')->warning('[TestPaymentService] Callback returned non-success status', [
                'request_id' => $requestId,
                'source' => $source,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'url' => $url,
            ]);
        } catch (\Throwable $e) {
            Log::channel('error')->error('[TestPaymentService] Callback failed', [
                'request_id' => $requestId,
                'source' => $source,
                'error' => $e->getMessage(),
                'url' => $url,
            ]);
        }

        return false;
    }

    /**
     * Avoid cron sending a failed callback while checkout is still waiting on the provider.
     */
    public static function shouldNotifyFailedFromCron(object $transaction, int $graceSeconds = 90): bool
    {
        if (! isset($transaction->created_at)) {
            return true;
        }

        $createdAt = $transaction->created_at;

        if ($createdAt instanceof \DateTimeInterface) {
            return $createdAt <= now()->subSeconds($graceSeconds);
        }

        return true;
    }

    public static function post(string $url, array $data, ?User $user = null, int $timeout = 120): Response
    {
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            $payload = json_encode((object) [], JSON_UNESCAPED_SLASHES);
        }

        $headers = [];
        if ($user && ! empty($user->api_key) && ! empty($user->api_secret)) {
            $headers['X-API-KEY'] = $user->api_key;
            $headers['X-SIGNATURE'] = hash_hmac('sha256', $payload, $user->api_secret);
        }

        return Http::timeout($timeout)
            ->withHeaders($headers)
            ->withBody($payload, 'application/json')
            ->post($url);
    }
}
