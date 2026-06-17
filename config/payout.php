<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payout checkout rate limits (requests per minute, per IP)
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'default_per_minute' => (int) env('PAYOUT_RATE_LIMIT_DEFAULT', 60),
        'vip_per_minute' => (int) env('PAYOUT_RATE_LIMIT_VIP', 200),
        'vip_ips' => array_filter(array_map('trim', explode(',', env(
            'PAYOUT_RATE_LIMIT_VIP_IPS',
            '18.138.132.207'
        )))),
    ],

];
