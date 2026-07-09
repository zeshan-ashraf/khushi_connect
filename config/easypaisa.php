<?php

return [
    'mode'=>env('EASYPAISA_MODE'),
    'type'=>env('EASYPAISA_TYPE'),
    'callback'=> env('EASYPAISA_CALLBACK_URL'),

    'sandbox_url'=>env('EASYPAISA_SANDBOX_URL'),
    'sandbox_username'=> env('EASYPAISA_SANDBOX_USERNAME'),
    'sandbox_password'=>env('EASYPAISA_SANDBOX_PASSWORD'),
    'sandbox_storeid'=>env('EASYPAISA_SANDBOX_STOREID'),
    'sandbox_hashkey'=> env('EASYPAISA_SANDBOX_HASHKEY'),

    'prod_username'=> env('EASYPAISA_PRODUCTION_USERNAME'),
    'prod_password'=>env('EASYPAISA_PRODUCTION_PASSWORD'),
    'prod_storeid'=> env('EASYPAISA_PRODUCTION_STOREID'),
    
    'prod_move_username'=> env('EASYPAISA_PRODUCTION_MOVE_USERNAME'),
    'prod_move_password'=>env('EASYPAISA_PRODUCTION_MOVE_PASSWORD'),
    'prod_move_storeid'=> env('EASYPAISA_PRODUCTION_MOVE_STOREID'),

    'prod_young_username'=> env('EASYPAISA_PRODUCTION_YOUNG_USERNAME'),
    'prod_young_password'=>env('EASYPAISA_PRODUCTION_YOUNG_PASSWORD'),
    'prod_young_storeid'=> env('EASYPAISA_PRODUCTION_YOUNG_STOREID'),

    'prod_desk_username'=> env('EASYPAISA_PRODUCTION_DESK_USERNAME'),
    'prod_desk_password'=>env('EASYPAISA_PRODUCTION_DESK_PASSWORD'),
    'prod_desk_storeid'=> env('EASYPAISA_PRODUCTION_DESK_STOREID'),
    
    'prod_hashkey'=> env('EASYPAISA_PRODUCTION_HASHKEY'),
    'prod_url'=> env('EASYPAISA_PRODUCTION_URL'),
    'status_inquiry_url' => env(
        'EASYPAISA_STATUS_INQUIRY',
        'https://easypay.easypaisa.com.pk/easypay-service/rest/v4/inquire-transaction'
    ),
    'account_num' => env('EASYPAISA_ACCOUNT_NUM'),

    'hosted'=> env('EASYPAISA_HOSTED_CHECKOUT'),

    /*
    |--------------------------------------------------------------------------
    | Easypaisa status crons — minimum transaction age before inquiry/recheck
    |--------------------------------------------------------------------------
    | Used by pending-status and recheck-status commands. Avoids racing checkout.
    */
    'cron_pending_min_age_minutes' => (int) env('EASYPAISA_CRON_PENDING_MIN_AGE_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Easypaisa payin pending-queue throttle (easypaisa.pending.limit middleware)
    |--------------------------------------------------------------------------
    | Block new Easypaisa payins when pending count >= block threshold.
    | While blocked, requests stay rejected until pending count <= resume threshold.
    */
    'pending_block_threshold' => (int) env('EASYPAISA_PENDING_BLOCK_THRESHOLD', 500),
    'pending_resume_threshold' => (int) env('EASYPAISA_PENDING_RESUME_THRESHOLD', 100),
    'pending_count_cache_minutes' => (int) env('EASYPAISA_PENDING_COUNT_CACHE_MINUTES', 1),
];