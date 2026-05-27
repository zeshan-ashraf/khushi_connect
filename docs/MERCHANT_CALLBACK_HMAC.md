# Merchant Callback Webhook — HMAC Verification Guide

This document explains how **Khushi Connect** signs payment and payout callbacks, and how your server should **verify** those callbacks before trusting the payload.

---

## 1. Overview

When a payin or payout reaches a final state, we send an **HTTP POST** to the `callback_url` you provided at checkout.

If your merchant account has **both** credentials configured:

| Credential   | Purpose                                      |
|-------------|------------------------------------------------|
| `api_key`   | Public identifier (sent in `X-API-KEY`)        |
| `api_secret`| Private signing key (never sent; keep secret)  |

…then each callback includes an **HMAC-SHA256 signature** so you can confirm the request really came from us and was not tampered with.

If either credential is missing, callbacks are sent **without** signature headers (legacy behaviour). No changes are required on your side for those accounts.

---

## 2. Callback request format

| Property        | Value                                      |
|----------------|---------------------------------------------|
| Method         | `POST`                                      |
| Content-Type   | `application/json`                          |
| Body           | Raw JSON string (see payload examples below)  |
| Signature header | `X-SIGNATURE` (hex-encoded HMAC-SHA256)   |
| API key header | `X-API-KEY` (your `api_key`)                |

### Example HTTP request

```http
POST /api/payment/callback HTTP/1.1
Host: your-shop.example.com
Content-Type: application/json
X-API-KEY: your_api_key_here
X-SIGNATURE: 8f4e2b1c9a0d3e7f6b5a4c3d2e1f0a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d4e3f2

{"orderId":"ORD-1001","tid":"987654","tRefNo":"T20260528120000abcde","amount":500,"status":"success"}
```

---

## 3. How we generate the signature

We use a single rule everywhere callbacks are sent (payin, payout, manual admin resend):

```
signature = HMAC-SHA256( raw_json_body , api_secret )
```

Details:

1. Build a PHP array with the callback fields (see [Payload reference](#6-payload-reference)).
2. Encode to JSON with **unescaped slashes**:
   ```php
   $rawBody = json_encode($data, JSON_UNESCAPED_SLASHES);
   ```
3. Compute:
   ```php
   $signature = hash_hmac('sha256', $rawBody, $api_secret);
   ```
4. Send **exactly** `$rawBody` as the request body (not a re-encoded copy).
5. Set headers `X-API-KEY` and `X-SIGNATURE`.

The signature is a **lowercase hexadecimal** string (64 characters), as produced by PHP `hash_hmac('sha256', ...)`.

---

## 4. How you verify (step by step)

On your callback endpoint:

1. Read the **raw request body** as a string.  
   Do **not** use form data or a parsed JSON object for signing.

2. Read headers:
   - `X-API-KEY` (or `HTTP_X_API_KEY` in PHP `$_SERVER`)
   - `X-SIGNATURE` (or `HTTP_X_SIGNATURE`)

3. **If both headers are absent**  
   Treat as a legacy unsigned callback (only if your account is not configured for HMAC). Process using your existing logic.

4. **If either header is present** (signed mode):
   - Confirm `X-API-KEY` matches your stored `api_key`.
   - Compute expected signature:
     ```
     expected = HMAC-SHA256( raw_body , your_api_secret )
     ```
   - Compare with `X-SIGNATURE` using a **timing-safe** comparison (`hash_equals` in PHP, `crypto.timingSafeEqual` in Node.js, `hmac.compare_digest` in Python).

5. If verification fails → respond with **401** and do **not** update orders or release goods.

6. If verification succeeds → `json_decode` the body and run your business logic (update order status, credit wallet, etc.).

7. Respond with **HTTP 2xx** when processed successfully. Non-2xx responses may trigger retries (payin automatic callbacks use retry with backoff).

---

## 5. Implementation examples

### 5.1 PHP (recommended)

```php
<?php

declare(strict_types=1);

$apiKey    = getenv('KHUSHI_API_KEY');
$apiSecret = getenv('KHUSHI_API_SECRET');

$rawBody = file_get_contents('php://input');
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$providedSig = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

// Signed callback
if ($providedKey !== '' || $providedSig !== '') {
    if ($providedKey === '' || $providedSig === '' || $apiSecret === '') {
        http_response_code(401);
        exit(json_encode(['ok' => false, 'error' => 'incomplete_signature']));
    }

    if (!hash_equals($apiKey, $providedKey)) {
        http_response_code(401);
        exit(json_encode(['ok' => false, 'error' => 'invalid_api_key']));
    }

    $expected = hash_hmac('sha256', $rawBody, $apiSecret);

    if (!hash_equals($expected, $providedSig)) {
        http_response_code(401);
        exit(json_encode(['ok' => false, 'error' => 'invalid_signature']));
    }
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    exit(json_encode(['ok' => false, 'error' => 'invalid_json']));
}

// Example: idempotent order update
$orderId = $payload['orderId'] ?? null;
$status  = $payload['status'] ?? null;

// ... your logic ...

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => true]);
```

### 5.2 Laravel

```php
public function handle(Request $request)
{
    $rawBody = $request->getContent(); // NOT $request->all()

    $key = $request->header('X-API-KEY');
    $sig = $request->header('X-SIGNATURE');

    if ($key || $sig) {
        if ($key !== config('khushi.api_key')) {
            return response()->json(['ok' => false], 401);
        }

        $expected = hash_hmac(
            'sha256',
            $rawBody,
            config('khushi.api_secret')
        );

        if (!hash_equals($expected, (string) $sig)) {
            return response()->json(['ok' => false], 401);
        }
    }

    $payload = json_decode($rawBody, true);

    // ...

    return response()->json(['ok' => true]);
}
```

### 5.3 Node.js (Express)

```javascript
const crypto = require('crypto');

function verifyKhushiCallback(req, apiKey, apiSecret) {
  const rawBody = req.body; // use express.raw({ type: 'application/json' }) middleware
  const providedKey = req.get('X-API-KEY') || '';
  const providedSig = req.get('X-SIGNATURE') || '';

  if (!providedKey && !providedSig) {
    return { ok: true, signed: false };
  }

  if (providedKey !== apiKey) {
    return { ok: false, error: 'invalid_api_key' };
  }

  const expected = crypto
    .createHmac('sha256', apiSecret)
    .update(rawBody, 'utf8')
    .digest('hex');

  const a = Buffer.from(expected, 'utf8');
  const b = Buffer.from(providedSig, 'utf8');

  if (a.length !== b.length || !crypto.timingSafeEqual(a, b)) {
    return { ok: false, error: 'invalid_signature' };
  }

  return { ok: true, signed: true, payload: JSON.parse(rawBody) };
}

// app.use('/callback', express.raw({ type: 'application/json' }), (req, res) => { ... });
```

### 5.4 Python (Flask)

```python
import hmac
import hashlib
import json
from flask import request, jsonify

API_KEY = "your_api_key"
API_SECRET = b"your_api_secret"  # bytes

@app.post("/callback")
def callback():
    raw_body = request.get_data()  # bytes
    provided_key = request.headers.get("X-API-KEY", "")
    provided_sig = request.headers.get("X-SIGNATURE", "")

    if provided_key or provided_sig:
        if provided_key != API_KEY:
            return jsonify(ok=False), 401

        expected = hmac.new(
            API_SECRET,
            raw_body,
            hashlib.sha256
        ).hexdigest()

        if not hmac.compare_digest(expected, provided_sig):
            return jsonify(ok=False), 401

    payload = json.loads(raw_body)
    # ... business logic ...
    return jsonify(ok=True)
```

---

## 6. Payload reference

Field presence depends on **payin vs payout** and **automatic vs manual** resend. Always verify the signature on the **raw body you receive**, then parse JSON.

### 6.1 Payin — automatic success callback

Sent when a payin completes successfully via the API flow.

```json
{
  "orderId": "your-merchant-order-id",
  "tid": "carrier-transaction-id",
  "tRefNo": "T20260528120000abcde",
  "amount": 500,
  "status": "success"
}
```

| Field     | Description                          |
|----------|--------------------------------------|
| orderId  | Your order ID from checkout          |
| tid      | Payment provider transaction ID    |
| tRefNo   | Our internal transaction reference |
| amount   | Transaction amount                   |
| status   | e.g. `success`, `failed`           |

### 6.2 Payin — manual admin resend

Admin “Send Callback” may send a **smaller** payload after status re-check:

```json
{
  "orderId": "your-merchant-order-id",
  "amount": 500,
  "status": "success"
}
```

### 6.3 Payout — API callback (success)

```json
{
  "orderId": "your-merchant-order-id",
  "tid": "payout-transaction-reference",
  "amount": 500,
  "status": "success"
}
```

### 6.4 Payout — API callback (failure examples)

May include a `message` field:

```json
{
  "orderId": "your-merchant-order-id",
  "tid": "optional-reference",
  "message": "Your payout cannot be processed due to ...",
  "status": "failed"
}
```

### 6.5 Payout — manual admin resend

```json
{
  "orderId": "your-merchant-order-id",
  "tid": "transaction_reference",
  "amount": 500,
  "status": "success"
}
```

---

## 7. Generate a test signature locally

Use the **exact** JSON string you expect to receive (same key order and values).

### PHP

```php
<?php

$apiSecret = 'your_api_secret';

$data = [
    'orderId' => 'ORD-1001',
    'tid' => '987654',
    'tRefNo' => 'T20260528120000abcde',
    'amount' => 500,
    'status' => 'success',
];

$rawBody = json_encode($data, JSON_UNESCAPED_SLASHES);
$signature = hash_hmac('sha256', $rawBody, $apiSecret);

echo "Body:\n$rawBody\n\n";
echo "X-SIGNATURE:\n$signature\n";
```

### OpenSSL (command line)

```bash
BODY='{"orderId":"ORD-1001","tid":"987654","tRefNo":"T20260528120000abcde","amount":500,"status":"success"}'
SECRET='your_api_secret'
echo -n "$BODY" | openssl dgst -sha256 -hmac "$SECRET"
```

### cURL test against your endpoint

```bash
BODY='{"orderId":"ORD-1001","amount":500,"status":"success"}'
SIG=$(php -r "echo hash_hmac('sha256', '$BODY', 'your_api_secret');")

curl -X POST "https://your-site.com/api/callback" \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: your_api_key" \
  -H "X-SIGNATURE: $SIG" \
  --data-raw "$BODY"
```

---

## 8. Why you must use the raw body

We sign the **exact byte sequence** of the JSON body. If you:

- Parse JSON and call `json_encode()` again, or
- Use a framework that reformats JSON,

…the string may differ (key order, spacing, number formatting, escaped slashes) and verification will **fail** even for legitimate callbacks.

**Correct:** sign and verify `file_get_contents('php://input')` / `request.getContent()` / `request.get_data()`.

**Incorrect:** `json_encode($request->all())` or `$request->json()->all()` then sign.

---

## 9. Security checklist

| Practice | Reason |
|----------|--------|
| Store `api_secret` only on the server | Never expose in mobile apps or front-end JS |
| Use `hash_equals` / timing-safe compare | Prevents timing attacks on signature |
| Reject failed verification with 401 | Do not process unauthenticated payloads |
| Make handlers **idempotent** | Same `orderId` + `status` may be delivered more than once |
| Use HTTPS for `callback_url` | Required for production integrations |
| Log verification failures, not secrets | Aids debugging without leaking keys |

We do **not** include timestamps, nonces, or algorithm headers in the current version. Verification is: **HMAC-SHA256 of the raw JSON body with your `api_secret`**.

---

## 10. Quick troubleshooting

| Symptom | Likely cause |
|--------|----------------|
| Signature always invalid | Verifying re-encoded JSON instead of raw body |
| Works in Postman, fails live | Body modified by middleware (XML parser, empty body cache) |
| Invalid only for some callbacks | Different payload shape (admin resend vs automatic); still verify raw body as received |
| No `X-SIGNATURE` header | Account missing `api_key` or `api_secret`; contact support to enable |
| `X-API-KEY` mismatch | Wrong key configured on your server |

---

## 11. Support

To enable HMAC signing, request **`api_key`** and **`api_secret`** for your merchant account. Both must be set on our side before signed callbacks are sent.

For integration help, provide:

- Your merchant email / client ID  
- Sample raw body (redact PII)  
- `X-SIGNATURE` you received  
- Signature you computed locally  

---

*Document version: 1.0 — matches `App\Support\MerchantCallback` implementation.*
