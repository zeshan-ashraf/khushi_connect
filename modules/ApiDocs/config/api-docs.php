<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand & URLs — edit per project (Nova Connect / Khushi / Mono)
    |--------------------------------------------------------------------------
    */
    'brand' => [
        'name' => env('API_DOCS_BRAND_NAME', 'Khushi Connect'),
        'logo' => env('API_DOCS_LOGO', 'assets/img/logo/logo-1.png'),
        'support_email' => env('API_DOCS_SUPPORT_EMAIL', 'info@khushiconnect.com'),
        'api_version' => env('API_DOCS_API_VERSION', 'v1'),
        'server_ip' => env('API_DOCS_SERVER_IP', 'YOUR_SERVER_IP'),
    ],

    'base_url' => env('API_DOCS_BASE_URL', 'https://khushiconnect.com/api'),

    /*
    |--------------------------------------------------------------------------
    | Docs navigation (design sidebar — do not change keys without updating routes)
    |--------------------------------------------------------------------------
    */
    'menu' => [
        [
            'id' => 'get-started',
            'label' => 'Get Started',
            'icon' => 'rocket',
        ],
        [
            'id' => 'payment-checkout',
            'label' => 'Payment Checkout',
            'icon' => 'credit-card',
        ],
        [
            'id' => 'payment-payout',
            'label' => 'Payment Payout',
            'icon' => 'send',
        ],
        [
            'id' => 'status-check',
            'label' => 'Status Check',
            'icon' => 'shield',
        ],
        [
            'id' => 'dashboard-data',
            'label' => 'Dashboard Data',
            'icon' => 'bar-chart-2',
        ],
        [
            'id' => 'callbacks',
            'label' => 'Callbacks',
            'icon' => 'git-branch',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Page content
    |--------------------------------------------------------------------------
    */
    'pages' => [

        'get-started' => [
            'title' => 'Get Started',
            'show_endpoint' => false,
            'description' => 'Welcome to the :brand API documentation. Use these endpoints to integrate JazzCash and Easypaisa payment collection (pay-in) and payout flows into your application.',
            'notices' => [
                [
                    'title' => 'Important — read before integrating',
                    'type' => 'mandatory',
                    'items' => [
                        'These are <strong>production APIs</strong>. If you need a real-time testing number, please contact us.',
                        'Our server IP: <code>:server_ip</code> — whitelist this IP on your side if required for callbacks.',
                        'For <strong>payouts</strong>, you must provide a list of your server IPs so we can whitelist them before payout access is enabled.',
                    ],
                ],
            ],
            'sections' => [
                [
                    'heading' => 'Authentication',
                    'body' => 'Payment Checkout and Payment Payout (v1) use <strong>HMAC</strong> authentication. Each client is issued a public API Key ID and a secret. Every request must include <code>X-API-Key-ID</code> and <code>X-HMAC-Signature</code> headers. Contact us for your <code>api_key</code> and <code>secret</code>. Signature string format: <code>request body + api_secret</code>, then apply HMAC-SHA256.',
                ],
                [
                    'heading' => 'HMAC signature (PHP example)',
                    'body' => '<pre><code>$body = json_encode($payload); // exact request body
$signature = hash_hmac(\'sha256\', $body, $apiSecret);
// Send headers: X-API-Key-ID, X-HMAC-Signature, Content-Type: application/json</code></pre>',
                ],
                [
                    'heading' => 'Base URL',
                    'body' => 'All API requests are sent to <code>:base_url</code>',
                ],
                [
                    'heading' => 'Request format',
                    'body' => 'Send parameters as <code>application/json</code> via <code>POST</code> (or <code>GET</code> where documented). Use lowercase values for <code>payment_method</code> and <code>payout_method</code> (<code>jazzcash</code>, <code>easypaisa</code>).',
                ],
                [
                    'heading' => 'Callbacks',
                    'body' => 'Provide an <code>https://</code> callback URL when initiating pay-in or payout. :brand will POST the transaction result to your URL when processing completes. See the Callbacks section for payload formats.',
                ],
                [
                    'heading' => 'Amount limits',
                    'body' => 'Refer to your commercial agreement for per-transaction limits. Validate amounts on your side before calling the API.',
                ],
                [
                    'heading' => 'Support',
                    'body' => 'Need help? Contact <a href="mailto::support_email">:support_email</a>',
                ],
            ],
        ],

        'payment-checkout' => [
            'title' => 'Payment Checkout (Pay In)',
            'method' => 'POST',
            'path' => '/v1/payment-checkout',
            'description' => 'Initiate a payment checkout process. This API uses HMAC (Hash-based Message Authentication Code) for request validation. Each client is issued a public API Key ID and a secret. Requests must include a signature based on the request contents and secret.',
            'headers' => [
                ['name' => 'X-API-Key-ID', 'type' => 'string', 'required' => true, 'description' => 'Your public API key ID (used to identify your client).'],
                ['name' => 'X-HMAC-Signature', 'type' => 'string', 'required' => true, 'description' => 'HMAC SHA256 signature generated using your private secret key over the request body.'],
                ['name' => 'Content-Type', 'type' => 'string', 'required' => true, 'description' => 'Must be <code>application/json</code>.'],
            ],
            'parameters' => [
                ['name' => 'orderId', 'type' => 'string', 'required' => true, 'description' => 'The unique identifier for the order.', 'example' => 'ORD123456'],
                ['name' => 'amount', 'type' => 'string', 'required' => true, 'description' => 'The total amount for the payment.', 'example' => '1000'],
                ['name' => 'phone', 'type' => 'string', 'required' => true, 'description' => 'The phone number of the customer.', 'example' => '03001234567'],
                ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'The email address of the customer.', 'example' => 'customer@mail.com'],
                ['name' => 'client_email', 'type' => 'string', 'required' => true, 'description' => 'Email will be provided by :brand administrator.', 'example' => 'client@mail.com'],
                ['name' => 'payment_method', 'type' => 'string', 'required' => true, 'description' => 'The payment method chosen for the transaction. Options: <code>easypaisa</code>, <code>jazzcash</code>.', 'example' => 'jazzcash'],
                ['name' => 'callback_url', 'type' => 'string', 'required' => true, 'description' => 'The URL where :brand will send the payment status after processing.', 'example' => 'https://yourcallback.com'],
            ],
            'request_example' => [
                'orderId' => 'update-test-01',
                'amount' => '5',
                'phone' => '032XXXXXXXX',
                'email' => '270785@gmail.com',
                'client_email' => 'abc@gmail.com',
                'payment_method' => 'easypaisa',
                'callback_url' => 'https://xyz.com/api/notify/order',
            ],
            'responses' => [
                [
                    'code' => 200,
                    'label' => 'Success',
                    'type' => 'success',
                    'description' => 'Payment checkout initiated successfully.',
                    'body' => [
                        'status' => 'success',
                        'message' => 'Payment checkout initiated',
                        'reference_id' => 'TXN-99887766',
                    ],
                ],
                [
                    'code' => 401,
                    'label' => 'Unauthorized',
                    'type' => 'error',
                    'description' => 'Missing authentication headers, invalid API key ID, invalid signature, request expired, or nonce already used.',
                    'body' => [
                        'error' => 'Missing authentication headers',
                    ],
                ],
                [
                    'code' => 400,
                    'label' => 'Error',
                    'type' => 'error',
                    'description' => 'Invalid input — missing or malformed JSON body.',
                    'body' => [
                        'status' => 'error',
                        'message' => 'Invalid input',
                    ],
                ],
            ],
            'error_codes' => [
                ['code' => '401', 'description' => 'Missing authentication headers — one or more required headers missing.'],
                ['code' => '401', 'description' => 'Invalid API key ID — API key ID not found or inactive.'],
                ['code' => '401', 'description' => 'Invalid signature — HMAC does not match expected.'],
                ['code' => '401', 'description' => 'Request expired — timestamp outside 5-minute window.'],
                ['code' => '401', 'description' => 'Nonce already used — replay attempt detected.'],
                ['code' => '400', 'description' => 'Invalid input — missing or malformed JSON body.'],
            ],
            'notes' => [
                'Ensure <code>payment_method</code> is either <code>easypaisa</code> or <code>jazzcash</code> (lowercase).',
                'All parameters are required to complete the payment checkout process.',
                'Sign the exact JSON request body with HMAC-SHA256 using your API secret.',
                'Example headers: <code>X-API-Key-ID: test_123</code>, <code>X-HMAC-Signature: aefb98a...</code> (64 hex chars).',
            ],
        ],

        'payment-payout' => [
            'title' => 'Payment Payout',
            'method' => 'POST',
            'path' => '/v1/payout/checkout',
            'description' => 'Initiate a payout transaction to the specified client using the selected payout method. This API uses HMAC for request validation. Each client is issued a public API Key ID and a secret. Requests must include a signature based on the request contents and secret.',
            'headers' => [
                ['name' => 'X-API-Key-ID', 'type' => 'string', 'required' => true, 'description' => 'Your public API key ID (used to identify your client).'],
                ['name' => 'X-HMAC-Signature', 'type' => 'string', 'required' => true, 'description' => 'HMAC SHA256 signature generated using your private secret key over the request body.'],
                ['name' => 'Content-Type', 'type' => 'string', 'required' => true, 'description' => 'Must be <code>application/json</code>.'],
            ],
            'parameters' => [
                ['name' => 'amount', 'type' => 'string', 'required' => true, 'description' => 'The amount to be paid out to the client.', 'example' => '5000.00'],
                ['name' => 'phone', 'type' => 'string', 'required' => true, 'description' => 'The phone number of the recipient for the payout.', 'example' => '923001234567'],
                ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'The email address of the customer.', 'example' => 'customer@mail.com'],
                ['name' => 'client_email', 'type' => 'string', 'required' => true, 'description' => 'Email will be provided from :brand administrator.', 'example' => 'client@mail.com'],
                ['name' => 'payout_method', 'type' => 'string', 'required' => true, 'description' => 'The payout method to be used. Options: <code>easypaisa</code>, <code>jazzcash</code>.', 'example' => 'jazzcash'],
                ['name' => 'callback_url', 'type' => 'string', 'required' => true, 'description' => 'The URL where :brand will send the payment status after processing.', 'example' => 'https://yourcallback.com'],
                ['name' => 'orderId', 'type' => 'string', 'required' => true, 'description' => 'The unique identifier for the order.', 'example' => 'ORD123456'],
            ],
            'request_example' => [
                'orderId' => 'your order id number',
                'amount' => '5000',
                'phone' => '923001234567',
                'email' => 'customer@mail.com',
                'client_email' => 'client@mail.com',
                'payout_method' => 'jazzcash',
                'callback_url' => 'your callback url',
            ],
            'responses' => [
                [
                    'code' => 200,
                    'label' => 'Success',
                    'type' => 'success',
                    'description' => 'If the payout process is successful, you will receive a response containing a confirmation and payout details.',
                    'body' => [
                        'status' => 'success',
                        'message' => 'Payout initiated successfully.',
                        'transaction_id' => 'T2024.....',
                    ],
                ],
                [
                    'code' => 401,
                    'label' => 'Unauthorized',
                    'type' => 'error',
                    'description' => 'Missing authentication headers, invalid API key ID, or invalid signature.',
                    'body' => [
                        'error' => 'Missing authentication headers',
                    ],
                ],
                [
                    'code' => 400,
                    'label' => 'Error',
                    'type' => 'error',
                    'description' => 'Missing or invalid parameters in the request.',
                    'body' => [
                        'status' => 'error',
                        'message' => 'Missing or invalid parameters.',
                        'error_code' => '400',
                    ],
                ],
            ],
            'error_codes' => [
                ['code' => '401', 'description' => 'Missing authentication headers — one or more required headers missing.'],
                ['code' => '401', 'description' => 'Invalid API key ID — API key ID not found or inactive.'],
                ['code' => '401', 'description' => 'Invalid signature — HMAC does not match expected.'],
                ['code' => '400', 'description' => 'Invalid input — missing or malformed JSON body.'],
                ['code' => '400', 'description' => 'Missing or invalid parameters in the request.'],
                ['code' => '500', 'description' => 'Internal server error while processing the request.'],
            ],
            'notes' => [
                'Ensure that the <code>payout_method</code> is either <code>easypaisa</code> or <code>jazzcash</code>.',
                'All parameters are required to complete the payout transaction.',
                'Double-check the phone number and email before submitting the request to ensure the payout goes to the correct recipient.',
                'Your server IPs must be whitelisted by :brand before payout access is enabled.',
            ],
        ],

        'dashboard-data' => [
            'title' => 'Dashboard Data',
            'method' => 'GET',
            'path' => '/v1/get-dashboard-data',
            'description' => 'This API endpoint is used to get data of dashboard for the authenticated merchant.',
            'headers' => [
                ['name' => 'Authorization', 'type' => 'string', 'required' => true, 'description' => 'Bearer token using your API key: <code>Bearer {api_key}</code>.'],
            ],
            'parameters' => [
                ['name' => 'client_email', 'type' => 'string', 'required' => true, 'description' => 'Your admin-provided email (as documented for dashboard access).', 'example' => 'client@mail.com'],
            ],
            'request_example' => [
                'client_email' => 'client@mail.com',
            ],
            'responses' => [
                [
                    'code' => 200,
                    'label' => 'Success',
                    'type' => 'success',
                    'description' => 'Dashboard stats for the merchant account.',
                    'body' => [
                        'Previous Balance' => '0',
                        'Payin' => '0',
                        'Payout' => '0',
                        'JC' => '0',
                        'EP' => '0',
                        'Total' => '0',
                        'USDT' => '0',
                        'Unsettled (After Fee)' => '0',
                    ],
                ],
                [
                    'code' => 401,
                    'label' => 'Unauthorized',
                    'type' => 'error',
                    'description' => 'API key is required or invalid.',
                    'body' => [
                        'error' => 'API key is required',
                    ],
                ],
            ],
            'notes' => [
                'v1 endpoint authenticates via <code>Authorization: Bearer {api_key}</code>.',
                'Legacy endpoint <code>GET /get-dashboard-data</code> accepts <code>client_email</code> as a request parameter.',
                'Values are formatted with thousand separators where applicable.',
            ],
        ],

        'status-check' => [
            'title' => 'Status Check',
            'show_endpoint' => false,
            'description' => 'Check the status of a pay-in (collection) or payout transaction by order ID.',
            'endpoints' => [
                [
                    'title' => 'Pay-in Status Check',
                    'method' => 'POST',
                    'path' => '/payin-status-check',
                    'description' => 'Returns pay-in transaction status for the given order ID.',
                    'parameters' => [
                        ['name' => 'orderId', 'type' => 'string', 'required' => true, 'description' => 'Your orderId which is used for the pay-in transaction.', 'example' => 'DPD.....'],
                    ],
                    'request_example' => [
                        'orderId' => 'DPD.....',
                    ],
                    'responses' => [
                        [
                            'code' => 200,
                            'label' => 'Success',
                            'type' => 'success',
                            'description' => 'Returns the transaction record (prefers successful status if multiple exist).',
                            'body' => [
                                'order' => [
                                    'orderId' => 'DPD.....',
                                    'amount' => '1000',
                                    'status' => 'success',
                                    'transactionId' => 'T2024...',
                                    'txn_ref_no' => '...',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Payout Status Check',
                    'method' => 'POST',
                    'path' => '/payout-status-check',
                    'description' => 'Returns payout transaction details for the given order ID.',
                    'parameters' => [
                        ['name' => 'orderId', 'type' => 'string', 'required' => true, 'description' => 'The order ID used when initiating payout.', 'example' => 'ORD123456'],
                    ],
                    'request_example' => [
                        'orderId' => 'ORD123456',
                    ],
                    'responses' => [
                        [
                            'code' => 200,
                            'label' => 'Success',
                            'type' => 'success',
                            'description' => 'Returns the payout record (prefers successful status if multiple exist).',
                            'body' => [
                                'order' => [
                                    'orderId' => 'ORD123456',
                                    'amount' => '5000',
                                    'status' => 'success',
                                    'transaction_reference' => 'T2024...',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'callbacks' => [
            'title' => 'Callbacks',
            'show_endpoint' => false,
            'description' => 'When a pay-in or payout completes, :brand sends an HTTP POST to your <code>callback_url</code> with the result payload.',
            'callback_sections' => [
                [
                    'title' => 'Pay-in (Payment Checkout) — Success',
                    'type' => 'success',
                    'body' => [
                        'orderId' => 'ORD123456',
                        'tid' => 'T2024...',
                        'tRefNo' => '...',
                        'amount' => '1000',
                        'status' => 'success',
                    ],
                ],
                [
                    'title' => 'Payout — Success',
                    'type' => 'success',
                    'body' => [
                        'orderId' => 'Your order_id',
                        'tid' => 'T2024...',
                        'amount' => '',
                        'status' => 'success',
                    ],
                ],
                [
                    'title' => 'Payout — Failed',
                    'type' => 'error',
                    'body' => [
                        'orderId' => 'Your order_id',
                        'tid' => 'T2024...',
                        'message' => '',
                        'status' => 'failed',
                    ],
                ],
            ],
            'notes' => [
                'Callbacks are sent as HTTP POST to the <code>callback_url</code> you provide in the original request.',
                'Your endpoint should respond with HTTP 2xx to acknowledge receipt.',
                'Pay-in callbacks include <code>orderId</code>, <code>tid</code>, <code>tRefNo</code>, <code>amount</code>, and <code>status</code>.',
                'Payout success callbacks include <code>orderId</code>, <code>tid</code>, <code>amount</code>, and <code>status</code>.',
                'Failed payout callbacks include a <code>message</code> field describing the failure reason.',
                'When your merchant account has API credentials, callbacks may also include <code>X-API-KEY</code> and <code>X-SIGNATURE</code> headers for verification.',
            ],
        ],

    ],

];
