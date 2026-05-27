<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * POST merchant callback/webhook payloads with optional HMAC signing.
 */
final class MerchantCallback
{
    public static function post(string $url, array $data, ?User $user = null, int $timeout = 120): Response
    {
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            $payload = json_encode((object)[], JSON_UNESCAPED_SLASHES);
        }

        $headers = [];
        if ($user && !empty($user->api_key) && !empty($user->api_secret)) {
            $headers['X-API-KEY'] = $user->api_key;
            $headers['X-SIGNATURE'] = hash_hmac('sha256', $payload, $user->api_secret);
        }

        return Http::timeout($timeout)
            ->withHeaders($headers)
            ->withBody($payload, 'application/json')
            ->post($url);
    }
}
