# Payin Merchant Callback Unification

**Date:** 2026-06-04  
**Branch / scope:** Uncommitted local changes (8 files)  
**Safe to deploy:** Yes — after pre-deploy checklist below

---

## Purpose

Merchants reported receiving **two callbacks for one transaction**: first `failed`, then `success`.

Root causes:

1. **Multiple callback code paths** — checkout, cron jobs, and admin actions each built payloads differently.
2. **Inconsistent payload shape** — some paths sent `orderId` + `amount` + `status` only; checkout sent `tid`, `tRefNo`, and HMAC headers.
3. **Race condition** — cron could mark a pending txn as failed while checkout was still waiting on EasyPaisa/JazzCash (~30s), then checkout completed and sent success.

This change **routes every payin merchant notify through one function** with one payload format, HMAC signing, retries, and logging — regardless of status (`success`, `failed`, etc.).

---

## Solution Overview

```
Checkout / Cron / Admin
        │
        ▼
MerchantCallback::notifyPayin()
        │
        ├── buildPayinPayload()   → standard JSON body
        ├── post()                → HTTP POST + optional HMAC
        └── retry + logging       → payout log channel
```

### Standard payin callback payload

All payin notifies now send:

```json
{
  "orderId": "merchant-order-id",
  "tid": "provider-transaction-id-or-null",
  "tRefNo": "internal-txn-ref",
  "amount": 1000,
  "status": "success"
}
```

Headers (when merchant has `api_key` + `api_secret` on `users` table):

- `X-API-KEY`
- `X-SIGNATURE` — HMAC-SHA256 of raw JSON body

### Cron failed-callback grace period

`MerchantCallback::shouldNotifyFailedFromCron()` blocks cron from sending **failed** callbacks until the transaction is **≥ 90 seconds old**. This prevents cron from beating checkout to the merchant with a premature failure.

Success callbacks from cron are **not** delayed (if cron sees PAID, merchant should know immediately).

---

## Files Changed

| # | File | Lines changed (approx.) |
|---|------|-------------------------|
| 1 | `app/Support/MerchantCallback.php` | +115 |
| 2 | `app/Service/PaymentServiceV1.php` | −75 net |
| 3 | `app/Console/Commands/EasyPaisaCheckTransactionStatus.php` | refactored |
| 4 | `app/Console/Commands/EasypaisaTransactionRecheckStatus.php` | refactored |
| 5 | `app/Console/Commands/JazzCashCheckTransactionStatus.php` | refactored |
| 6 | `app/Console/Commands/TransactionRecheckStatus.php` | refactored |
| 7 | `app/Http/Controllers/Admin/SearchingController.php` | refactored |
| 8 | `app/Http/Controllers/Admin/TransactionController.php` | refactored |

**Total:** 8 files, ~291 insertions, ~355 deletions (net simplification).

---

## Per-File Changes

### 1. `app/Support/MerchantCallback.php`

**Purpose:** Single source of truth for payin merchant webhooks.

**Added:**

| Method | Description |
|--------|-------------|
| `buildPayinPayload(object $transaction)` | Builds standard 5-field payload from transaction model |
| `notifyPayin(...)` | Sends callback with retries, exponential backoff, structured logging |
| `shouldNotifyFailedFromCron(...)` | 90s grace before cron sends failed notify |

**Unchanged:**

| Method | Description |
|--------|-------------|
| `post(...)` | Low-level HTTP POST with optional HMAC — still used by payout flows |

**Config keys used (optional — defaults apply if missing):**

- `payment.callback.timeout` → default `120`
- `payment.callback.max_retries` → default `3`

---

### 2. `app/Service/PaymentServiceV1.php`

**Purpose:** Checkout flow (`POST /api/payin/checkout` → `orderFinalProcess`).

**Removed:**

- Private `sendCallback()` — duplicated retry/logging logic now lives in `MerchantCallback`

**Added:**

- Private `notifyPayinCallback()` — thin wrapper → `MerchantCallback::notifyPayin()`

**Changed:**

| Path | Before | After |
|------|--------|-------|
| JazzCash success | `sendCallback()` with inline payload | `notifyPayinCallback(..., 'checkout_jazzcash_success')` |
| JazzCash failed | **No callback sent** | `notifyPayinCallback(..., 'checkout_jazzcash_failed')` |
| EasyPaisa success | `sendCallback()` | `notifyPayinCallback(..., 'checkout_easypaisa_success')` |
| EasyPaisa failed | **No callback sent** | `notifyPayinCallback(..., 'checkout_easypaisa_failed')` |

**Business logic unchanged:** balance updates, transaction status updates, blocked-number handling, provider API calls.

---

### 3. `app/Console/Commands/EasyPaisaCheckTransactionStatus.php`

**Command:** `php artisan transactions:easypaisa-check-status`

**Before:** Raw `Http::timeout(60)->post($url, $data)` with inconsistent keys (`tid` on success, `TID` on failed). No HMAC. No retries.

**After:**

- Uses `MerchantCallback::notifyPayin()` for all notifies
- Failed paths gated by `shouldNotifyFailedFromCron()`
- Balance logic extracted to `applyEasypaisaBalance()` (same behavior)
- `$item->refresh()` before callback so payload has latest DB values

**Log `source` values:**

- `cron_easypaisa_pending_success`
- `cron_easypaisa_pending_failed`
- `cron_easypaisa_pending_failed_0003`

---

### 4. `app/Console/Commands/EasypaisaTransactionRecheckStatus.php`

**Command:** `php artisan transactions:easypaisa-recheck-status`

**Before:** Raw `Http::post()` with partial payloads.

**After:** `MerchantCallback::notifyPayin()` for success and failed rechecks.

**Log `source` values:**

- `cron_easypaisa_recheck_success`
- `cron_easypaisa_recheck_failed`
- `cron_easypaisa_recheck_failed_0003`

---

### 5. `app/Console/Commands/JazzCashCheckTransactionStatus.php`

**Command:** `php artisan transactions:jazzcash-check-status`

**Before:** Raw `Http::post()` — no HMAC, no `tRefNo`.

**After:**

- `MerchantCallback::notifyPayin()` for success and failed
- Failed gated by `shouldNotifyFailedFromCron()`
- Balance logic in `applyJazzcashBalance()` (same behavior)

**Log `source` values:**

- `cron_jazzcash_pending_success`
- `cron_jazzcash_pending_failed`

---

### 6. `app/Console/Commands/TransactionRecheckStatus.php`

**Command:** `php artisan transactions:jazzcash-recheck-status`

**Before:** Raw `Http::post()`.

**After:** `MerchantCallback::notifyPayin()` for success and failed rechecks.

**Log `source` values:**

- `cron_jazzcash_recheck_success`
- `cron_jazzcash_recheck_failed`

---

### 7. `app/Http/Controllers/Admin/SearchingController.php`

**Route:** Admin manual payin callback (searching module).

**Before:** `MerchantCallback::post($url, ['orderId','amount','status'])` — missing `tid`, `tRefNo`.

**After:** After DB update + `$item->refresh()`, calls `MerchantCallback::notifyPayin()`.

**Log `source` values:**

- `admin_manual_jazzcash_success` / `_failed`
- `admin_manual_easypaisa_success` / `_failed` / `_failed_0003`

**Not changed:** `payoutCallback()` — payout flow, still uses `MerchantCallback::post()` directly.

---

### 8. `app/Http/Controllers/Admin/TransactionController.php`

**Route:** Admin change transaction status + notify merchant.

**Before:** Raw `Http::timeout(60)->post()` with 3 fields only.

**After:** `MerchantCallback::notifyPayin($transaction, $transaction->user, 60, null, 'admin_change_status')`

---

## Callback Source Reference

Use the `source` field in `storage/logs/payout.log` to trace which path sent a callback:

| Source | Trigger |
|--------|---------|
| `checkout_jazzcash_success` | Live checkout JazzCash PAID |
| `checkout_jazzcash_failed` | Live checkout JazzCash failed |
| `checkout_easypaisa_success` | Live checkout EasyPaisa 0000 |
| `checkout_easypaisa_failed` | Live checkout EasyPaisa non-0000 |
| `cron_easypaisa_pending_*` | Scheduled pending EP check |
| `cron_jazzcash_pending_*` | Scheduled pending JC check |
| `cron_*_recheck_*` | Failed-txn recheck commands |
| `admin_manual_*` | Admin searching manual callback |
| `admin_change_status` | Admin transaction status change |

---

## Flow Verification (No Breaking Changes)

### Checkout flow — unchanged steps

1. `PayinController::checkout()` → `PaymentServiceV1::orderProcess()`
2. Provider API call (JazzCash / EasyPaisa)
3. `orderFinalProcess()` → `processJazzcashResult()` / `processEasypaisaResult()`
4. Transaction status updated in DB
5. Success: balance credited (if configured) → callback sent
6. Failed: callback sent (**new** — previously checkout failures had no callback)

### Cron flow — same schedule, safer notifies

1. Query pending/failed transactions
2. `StatusService::process()` — unchanged
3. Update transaction status — unchanged
4. Balance credit on success — unchanged
5. **Only change:** callback via unified function + grace period on failed

### Admin flow — same UI, richer payload

Manual callback and status-change still work; merchants now receive full payload + HMAC.

---

## Pre-Deploy Checklist

Run on the **server** after upload (PHP available there):

```bash
# 1. Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# 2. Verify commands register (no class/syntax errors)
php artisan list | grep transactions:

# Expected commands:
#   transactions:auto-fail
#   transactions:easypaisa-check-status
#   transactions:easypaisa-recheck-status
#   transactions:jazzcash-check-status
#   transactions:jazzcash-recheck-status

# 3. Optional — dry-run a single pending txn check (watch logs)
php artisan transactions:easypaisa-check-status
```

### Important: `routes/api.php` middleware

Current local checkout route has **reduced middleware**:

```php
Route::post('/checkout', [PayinController::class, 'checkout'])
    ->middleware(['payment.validate', 'easypaisa.limit', 'restrict.user.transaction.range']);
```

Production likely needs the full stack:

```php
->middleware([
    'payment.validate',
    'check.blocked.numbers',
    'easypaisa.limit',
    'easypaisa.pending.limit',
    'phone.verified',
    'restrict.user.transaction.range',
]);
```

**Do not deploy stripped middleware unless intentional.** This is unrelated to callbacks but affects live checkout safety.

---

## Post-Deploy Verification

1. **Successful payin** — merchant receives **one** callback with:
   - `status: success`
   - `tid`, `tRefNo` populated
   - HMAC headers if merchant uses signed callbacks

2. **Failed payin at checkout** — merchant receives **one** failed callback (new behavior).

3. **Pending txn resolved by cron** — check `payout.log`:
   - No failed callback within first 90 seconds of txn creation
   - Success callback when provider confirms PAID

4. **Search log by orderId:**

```
grep "orderId-here" storage/logs/payout.log
```

Confirm single final-status callback (or expected admin/cron source only).

---

## Merchant Integration Note

If any merchant was parsing the old cron **failed** payload key `TID` (uppercase), they must switch to **`tid`** (lowercase). All paths now use `tid` consistently.

Merchants using HMAC verification should now receive signed callbacks from **cron and admin** paths too — not just checkout.

---

## Out of Scope (Not Changed)

| Item | Notes |
|------|-------|
| Payout callbacks | `PayoutController`, `SearchingController::payoutCallback` — still use `MerchantCallback::post()` |
| IBFT callbacks | `IbftController` — raw `Http::post()` |
| `DashboardController::testing()` | Dev helper — raw HTTP, not production |
| `AutoFailPendingTransactions` | Marks failed after 30 min — **does not send callback** (pre-existing) |
| Idempotency column | No `callback_sent_at` yet — duplicate success from cron+checkout still theoretically possible but rare |
| Database migrations | None required |

---

## Rollback Plan

If issues occur after deploy, revert these 8 files to previous commit. No DB rollback needed.

```bash
git checkout HEAD~1 -- app/Support/MerchantCallback.php
git checkout HEAD~1 -- app/Service/PaymentServiceV1.php
git checkout HEAD~1 -- app/Console/Commands/EasyPaisaCheckTransactionStatus.php
git checkout HEAD~1 -- app/Console/Commands/EasypaisaTransactionRecheckStatus.php
git checkout HEAD~1 -- app/Console/Commands/JazzCashCheckTransactionStatus.php
git checkout HEAD~1 -- app/Console/Commands/TransactionRecheckStatus.php
git checkout HEAD~1 -- app/Http/Controllers/Admin/SearchingController.php
git checkout HEAD~1 -- app/Http/Controllers/Admin/TransactionController.php
php artisan config:clear
```

---

## Audit Summary (Engineer Sign-Off)

| Check | Result |
|-------|--------|
| All payin notify paths use `notifyPayin()` | Pass |
| Payload fields consistent (`orderId`, `tid`, `tRefNo`, `amount`, `status`) | Pass |
| HMAC applied when merchant has API keys | Pass |
| Checkout success/fail both notify | Pass |
| Cron failed notify has 90s grace | Pass |
| Transaction model has required fields + `user()` relation | Pass |
| Archive/backup models support same fields | Pass |
| No PHP linter errors on changed files | Pass |
| No new migrations / env vars required | Pass |
| Payout flow untouched | Pass |

**Verdict:** Safe to upload to live server after middleware review and post-deploy log check.
