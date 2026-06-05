# Payin Checkout — All Restrictions & Limits

**Document purpose:** Complete reference of every restriction applied to the payin checkout API.  
**Last updated:** June 2026  
**Primary route:** `POST /api/payin/checkout`  
**Controller:** `App\Http\Controllers\Api\PayinController@checkout`  
**Route file:** `routes/api.php` (line 45)

---

## 1. Route Overview

### Main checkout endpoint

| Item | Value |
|------|--------|
| **HTTP method** | POST |
| **URL path** | `/api/payin/checkout` |
| **Route name** | `payin.checkout` |

### Middleware stack (runs in this order)

1. `payment.validate`
2. `check.blocked.numbers`
3. `easypaisa.limit`
4. `easypaisa.pending.limit`
5. `phone.verified`
6. `restrict.user.transaction.range`

After middleware passes, `PayinController::checkout()` runs additional checks and post-payment rules.

### Related route (v1 — fewer restrictions)

| Item | Value |
|------|--------|
| **URL** | `POST /api/v1/payment-checkout` |
| **Auth** | HMAC (`hmac.authenticate` group) |
| **Middleware** | `phone.verified`, `restrict.user.transaction.range` only |
| **Missing vs main route** | No `payment.validate`, `check.blocked.numbers`, `easypaisa.limit`, `easypaisa.pending.limit` on this v1 route |

---

## 2. Request Validation (`payment.validate`)

**File:** `app/Http/Middleware/PaymentValidationMiddleware.php`

Runs first. Validates input and loads the merchant user onto the request as `user_model`.

### Required fields

| Field | Rules |
|-------|--------|
| `client_email` | Required, valid email (RFC + DNS) |
| `payment_method` | Required, must be `jazzcash` or `easypaisa` |
| `phone` | Required, must match `03XXXXXXXXX` (11 digits, starts with 03) |
| `callback_url` | Required, valid URL, must start with `https://` |
| `orderId` | Required, string, max 50 chars, must be unique in `transactions` table |
| `amount` | Required, numeric — see amount limits below |

### Amount limits (per transaction)

| Payment method | Minimum | Maximum |
|----------------|---------|---------|
| **Easypaisa** | 1 PKR | 100,000 PKR |
| **JazzCash** | 1 PKR | 50,000 PKR |

### Error responses

| Condition | HTTP status | Message |
|-----------|-------------|---------|
| Validation failed | 422 | `Validation failed` (+ `errors` object) |
| User email not found | 404 | `User not found.` |

---

## 3. Blocked Phone Numbers (`check.blocked.numbers`)

**File:** `app/Http/Middleware/CheckedBlockedNumbersMiddleware.php`  
**Data table:** `blocked_numbers`  
**Model:** `App\Models\BlockedNumber`

### When it applies

- Checks `phone` + `payment_method` against the `blocked_numbers` table.
- **JazzCash:** Skipped entirely when `config/blocked_numbers.php` → `jazzcash_blocking_enabled` is `false` (current default in repo).
- **Easypaisa:** Always checked by middleware (not gated by that config flag).

### Block conditions

A request is rejected when a matching record exists AND either:

- `is_permanent = true`, OR  
- `block_until` is in the future

### On blocked attempt

1. Returns HTTP **400** with message: `This number is currently blocked. Please try again later.`
2. Creates a failed transaction with `status = blocked` (if user is resolved)
3. Increments attempt counters on the blocked record
4. Escalates temporary block duration using progressive rules:

| Attempt count | Block duration |
|---------------|----------------|
| 1 | 24 hours |
| 2–3 | 7 days |
| 4+ | Permanent |

---

## 4. Easypaisa Monthly Volume Limit (`easypaisa.limit`)

**File:** `app/Http/Middleware/EasyPaisaLimitMiddleware.php`

### When it applies

- **Only** when `payment_method = easypaisa`
- **Only** for client user IDs listed in `$restrictedClientIds` (currently `[0]` in code — effectively inactive unless client ID 0 is used)

### Limit

| Setting | Value |
|---------|--------|
| Monthly cap | **480,000,000 PKR** (480M) |
| Period | Current calendar month |
| Counts | Successful Easypaisa payins from `transactions`, `archeive_transactions`, and `backup_transactions` |

### Error response

| HTTP status | Message |
|-------------|---------|
| 429 | `Monthly EasyPaisa limit has been reached. Please contact support.` |
| Error code | `MONTHLY_LIMIT_EXCEEDED` |

**Note:** Result is cached for 10 minutes per month key.

---

## 5. Easypaisa Pending Queue Limit (`easypaisa.pending.limit`)

**File:** `app/Http/Middleware/EasypaisaPendingRequestsMiddleware.php`

### When it applies

- **Only** when `payment_method = easypaisa`

### Limit

| Setting | Value |
|---------|--------|
| Max pending Easypaisa transactions | **400** |
| Count source | `transactions` where `status = pending` and `txn_type = easypaisa` |
| Cache TTL | 5 minutes |

### Error response

| HTTP status | Message |
|-------------|---------|
| 429 | `System is processing pending Easypaisa requests. Please wait a few minutes and try again.` |

---

## 6. Phone Verification Gate (`phone.verified`)

**File:** `app/Http/Middleware/EnsurePhoneIsVerified.php`  
**Service:** `App\Services\PhoneVerificationService`  
**Config:** `config/phone_verification.php`

### When it applies

- **Only** when `payment_method = easypaisa`
- JazzCash requests skip this middleware entirely

### Bypass conditions

- Merchant user has `new_user_verification = false` → verification not required
- Phone is already in verified numbers list → allowed through
- Request amount ≤ merchant `new_user_max_amount` (for unverified phones) → allowed through

### Concurrent request lock

| Setting | Value |
|---------|--------|
| Lock key | Per normalized phone |
| Lock duration | 5 seconds (`lock_seconds` in config) |

If lock cannot be acquired → HTTP **429**, message: `Request already processing`

### Unverified new user response

If phone is not verified AND amount exceeds `new_user_max_amount`:

| HTTP status | Response |
|-------------|----------|
| 200 | `{ "status": "new_user" }` — checkout does not proceed to payment |

---

## 7. Per-Merchant Transaction Amount Range (`restrict.user.transaction.range`)

**File:** `app/Http/Middleware/RestrictUserTransactionRange.php`

### When it applies

- Only for users with `user_role = Client`
- Only when `transaction_limit_enabled = true` on the user
- For Easypaisa with `new_user_verification = true`: only applies if phone is **verified** (unverified users are limited by `new_user_max_amount` in middleware #6 instead)

### Limits

Uses per-merchant fields:

- `transaction_amount_min`
- `transaction_amount_max`

Valid configuration: min ≥ 1, max ≤ 50,000, min < max

### Error responses

| Condition | HTTP status | Message |
|-----------|-------------|---------|
| Misconfigured limits | 422 | `Transaction limits are misconfigured for this merchant. Please contact support.` |
| Non-numeric amount | 422 | `Amount is required and must be numeric.` |
| Amount out of range | 422 | `Invalid transaction amount. Allowed range: {min} to {max}.` |

---

## 8. Controller-Level Restrictions (`PayinController`)

**File:** `app/Http/Controllers/Api/PayinController.php`

These run **after** all middleware passes.

### 8.1 API access / daily limit flag

**Method:** `validateApiAccess()`

| Condition | Result |
|-----------|--------|
| JazzCash + `user.jc_api = 0` | Rejected |
| Easypaisa + `user.ep_api = 0` | Rejected |

| HTTP status | Message |
|-------------|---------|
| 400 | `Error:Daily limit exceeded.` |

*(Internal meaning: API suspended for that payment method on the merchant account.)*

---

### 8.2 Post-success phone cooldown

**Method:** `blockNumberAfterSuccess()` → `BlockedNumber::handleSuccessfulTransaction()`

After a **successful** payin (JazzCash or Easypaisa):

| Setting | Value |
|---------|--------|
| Cooldown duration | **2 minutes** |
| Stored in | `blocked_numbers.block_until` |
| Reason | `Successful transaction cooldown` |

**JazzCash note:** Skipped when `jazzcash_blocking_enabled = false`.

---

### 8.3 JazzCash failure — insufficient balance block

**Trigger:** Response code `999` + message contains insufficient balance text  
**Method:** `BlockedNumber::handleInsufficientBalance()`  
**Requires:** `jazzcash_blocking_enabled = true`

| Attempt | Block duration |
|---------|----------------|
| 1st | 180 seconds (3 minutes) |
| 2nd | 1 hour |
| 3rd | 24 hours |
| 4th+ | 24 hours (each attempt) |

---

### 8.4 JazzCash failure — manual cancellation / late MPIN

**Trigger:** Response code `999` + user cancelled via SMS "N"  
**Method:** `BlockedNumber::handleManualCancellation()`  
**Requires:** `jazzcash_blocking_enabled = true`

| Rule | Block duration |
|------|----------------|
| 1st cancellation in a day | Logged, no block |
| 2nd cancellation same day | 1 hour block |

---

### 8.5 JazzCash upstream rate limit

**Trigger:** JazzCash HTTP response status **429**

| HTTP status | Message |
|-------------|---------|
| 502 | `Upstream provider rate limited the request.` |

---

### 8.6 Disabled / commented restrictions

The following exist in code but are **currently commented out**:

| Rule | Status |
|------|--------|
| Block number when JazzCash account does not exist | Commented out |
| Block number when Easypaisa account does not exist (code 0014) | Commented out |
| `isNumberBlocked()` method in controller | Defined but never called (middleware handles blocking) |

---

## 9. Configuration Flags

| Config file | Key | Current default | Effect |
|-------------|-----|-----------------|--------|
| `config/blocked_numbers.php` | `jazzcash_blocking_enabled` | `false` | When false: JazzCash skips blocked-number middleware, post-success cooldown, insufficient balance blocks, and manual cancellation blocks |
| `config/phone_verification.php` | `lock_seconds` | `5` | Concurrent Easypaisa request lock per phone |
| `config/phone_verification.php` | `cache_ttl_hours` | `2` | Verified phone cache TTL |

---

## 10. Complete Restriction Summary Table

| Restriction | Easypaisa | JazzCash | Where applied |
|-------------|-----------|----------|---------------|
| Phone format `03XXXXXXXXX` | Yes | Yes | Middleware: payment.validate |
| Amount min 1 | Yes | Yes | Middleware: payment.validate |
| Amount max 100,000 / 50,000 | 100k | 50k | Middleware: payment.validate |
| Unique orderId | Yes | Yes | Middleware: payment.validate |
| HTTPS callback URL | Yes | Yes | Middleware: payment.validate |
| Blocked numbers check | Yes | Yes* | Middleware: check.blocked.numbers |
| Monthly 480M cap | Yes** | No | Middleware: easypaisa.limit |
| Max 400 pending EP txns | Yes | No | Middleware: easypaisa.pending.limit |
| Phone verification gate | Yes | No | Middleware: phone.verified |
| Per-merchant min/max amount | Yes*** | Yes*** | Middleware: restrict.user.transaction.range |
| API access flag (jc_api / ep_api) | ep_api | jc_api | Controller |
| 2-min cooldown after success | Yes | Yes* | Controller + BlockedNumber model |
| Insufficient balance block | No | Yes* | Controller (JazzCash failures) |
| Manual cancellation block | No | Yes* | Controller (JazzCash failures) |
| Upstream 429 handling | No | Yes | Controller (JazzCash) |

\* JazzCash blocked-number features depend on `jazzcash_blocking_enabled`  
\** Only for client IDs in restricted list (currently `[0]`)  
\*** Only when `transaction_limit_enabled` on merchant user

---

## 11. Middleware Execution Flow (Quick Reference)

```
POST /api/payin/checkout
        │
        ▼
[1] payment.validate          → field validation, load user
        │
        ▼
[2] check.blocked.numbers     → reject if phone blocked
        │
        ▼
[3] easypaisa.limit           → EP monthly cap (if applicable)
        │
        ▼
[4] easypaisa.pending.limit   → EP pending queue cap
        │
        ▼
[5] phone.verified            → EP verification / new_user gate
        │
        ▼
[6] restrict.user.transaction.range → per-merchant amount range
        │
        ▼
PayinController::checkout()
        │
        ├─ validateApiAccess()     → jc_api / ep_api
        ├─ process payment
        ├─ on success → 2-min cooldown block
        └─ on JC failure → insufficient balance / cancellation blocks
```

---

## 12. Source File Index

| Component | File path |
|-----------|-----------|
| Route definition | `routes/api.php` |
| Payin controller | `app/Http/Controllers/Api/PayinController.php` |
| Payment validation | `app/Http/Middleware/PaymentValidationMiddleware.php` |
| Blocked numbers middleware | `app/Http/Middleware/CheckedBlockedNumbersMiddleware.php` |
| Easypaisa monthly limit | `app/Http/Middleware/EasyPaisaLimitMiddleware.php` |
| Easypaisa pending limit | `app/Http/Middleware/EasypaisaPendingRequestsMiddleware.php` |
| Phone verification | `app/Http/Middleware/EnsurePhoneIsVerified.php` |
| Merchant amount range | `app/Http/Middleware/RestrictUserTransactionRange.php` |
| Blocked number logic | `app/Models/BlockedNumber.php` |
| Blocked txn creation | `app/Service/PaymentService.php` → `createBlockedTransaction()` |
| JazzCash blocking toggle | `config/blocked_numbers.php` |
| Phone verification config | `config/phone_verification.php` |

---

*End of document*
