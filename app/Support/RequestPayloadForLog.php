<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Builds a copy of request input suitable for log channels (excludes Eloquent models
 * such as merged user_model / transaction_model, which otherwise bloat log files).
 */
final class RequestPayloadForLog
{
    /**
     * @return array<string, mixed>
     */
    public static function from(Request $request): array
    {
        $payload = [];
        foreach ($request->all() as $key => $value) {
            if ($value instanceof Model) {
                continue;
            }
            $payload[$key] = $value;
        }

        return $payload;
    }
}
