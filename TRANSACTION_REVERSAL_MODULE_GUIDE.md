# Transaction Reversal Module - Complete Guide

## Overview
The Transaction Reversal Module allows administrators to reverse successful transactions with a 6-hour waiting period (configurable). Transactions can be marked for reversal, cancelled before the deadline, or reversed immediately.

## Architecture

### How It Works
1. **Mark for Reversal**: Admin marks a successful transaction for reversal
2. **Waiting Period**: Transaction waits for 6 hours (configurable via `REVERSAL_WAIT_HOURS` in `.env`)
3. **Auto-Reversal**: After 6 hours, a scheduled command automatically reverses the transaction
4. **Manual Actions**: Admin can cancel reversal request or reverse immediately (bypass wait)

---

## Required Files

### 1. **Core Service** (Business Logic)
**File**: `app/Services/TransactionReversalService.php`
- **Purpose**: Contains all reversal business logic
- **Key Methods**:
  - `markForReversal()` - Mark transaction for reversal
  - `cancelReversal()` - Cancel reversal request
  - `reverseNow()` - Reverse immediately
  - `processAutoReversals()` - Auto-reverse after 6 hours
  - `getPendingReversals()` - Get all pending reversals
  - `getRemainingTime()` - Calculate countdown timer
  - `getTableType()` - Determine which table contains transaction

### 2. **Controller** (HTTP Endpoints)
**File**: `app/Http/Controllers/Admin/TransactionReversalController.php`
- **Purpose**: Handles HTTP requests for reversal operations
- **Routes**:
  - `GET /admin/transaction/reversal/list` - View pending reversals page
  - `POST /admin/transaction/reversal/mark` - Mark transaction for reversal
  - `POST /admin/transaction/reversal/cancel` - Cancel reversal request
  - `POST /admin/transaction/reversal/reverse-now` - Reverse immediately
  - `POST /admin/transaction/reversal/bulk-reverse` - Bulk reverse multiple transactions
- **Middleware**: Requires `Reverse Transactions` permission

### 3. **DataTable** (Data Display)
**File**: `app/DataTables/Admin/ReversalDataTable.php`
- **Purpose**: Formats and displays pending reversals in a table
- **Features**:
  - Shows countdown timer
  - Bulk selection checkboxes
  - Action buttons (Cancel, Reverse Now)
  - Excel export

### 3a. **Transaction Search DataTable** (Mark for Reversal Button)
**File**: `app/DataTables/Admin/SearchingDataTable.php` (lines 27-57)
- **Purpose**: Adds "Mark for Reversal" button to search results
- **Location**: In the `detail` column edit function
- **Logic**:
  - Checks if user has "Reverse Transactions" permission
  - Only shows for transactions with `status = 'success'`
  - Only shows if `reverse_requested_at` is null (not already marked)
  - Button includes `data-id` and `data-table-type` attributes
  - Works across `transactions`, `archeive_transactions`, and `backup_transactions` (union query)

### 3b. **Transaction List DataTable** (Mark for Reversal Button)
**File**: `app/DataTables/Admin/TransactionDataTable.php` (lines 29-59)
- **Purpose**: Adds "Mark for Reversal" button to transaction list page
- **Location**: In the `status-inqury` column edit function
- **Logic**:
  - Checks if user has "Reverse Transactions" permission
  - Only shows for transactions with `status = 'success'`
  - Only shows if `reverse_requested_at` is null (not already marked)
  - Button includes `data-id` and `data-table-type` attributes
  - Uses `transactions` table type

### 4. **View** (UI)
**File**: `resources/views/admin/transaction/reversals.blade.php`
- **Purpose**: Admin interface for managing reversals
- **Features**:
  - DataTable with countdown timers
  - Bulk actions (Reverse Selected, Cancel Selected)
  - Real-time countdown JavaScript
  - AJAX actions for all operations

### 4a. **Transaction Search Page** (Mark for Reversal UI)
**File**: `resources/views/admin/searching/list.blade.php` (lines 136-165)
- **Purpose**: Contains JavaScript handler for "Mark for Reversal" button
- **Features**:
  - AJAX handler for `.mark-for-reversal-btn` click event
  - Confirmation dialog before marking
  - Reloads page after successful marking
  - Error handling with alerts
- **Route Used**: `admin.transaction.reversal.mark`

### 4b. **Transaction List Page** (Mark for Reversal UI)
**File**: `resources/views/admin/transaction/list.blade.php` (lines 182-208)
- **Purpose**: Contains JavaScript handler for "Mark for Reversal" button
- **Features**:
  - AJAX handler for `.mark-for-reversal-btn` click event
  - Confirmation dialog before marking
  - Reloads page after successful marking
  - Error handling with alerts
- **Route Used**: `admin.transaction.reversal.mark`

### 4c. **Sidebar Menu** (Navigation)
**File**: `resources/views/admin/layout/include/sidebar.blade.php` (lines 69-74)
- **Purpose**: Adds "Pending Reversals" menu item to admin sidebar
- **Code**:
```php
@can('Reverse Transactions')
    <li class="@if (url()->current() == route('admin.transaction.reversal.list')) active @endif nav-item">
        <a class="d-flex align-items-center" href="{{ route('admin.transaction.reversal.list') }}">
            <i data-feather="rotate-ccw"></i>Pending Reversals
        </a>
    </li>
@endcan
```
- **Icon**: `rotate-ccw` (rotate counter-clockwise)
- **Permission**: Requires "Reverse Transactions" permission

### 5. **Console Command** (Scheduled Task)
**File**: `app/Console/Commands/AutoReverseTransactions.php`
- **Purpose**: Automatically reverses transactions after 6-hour wait
- **Command**: `php artisan transactions:auto-reverse`
- **Schedule**: Runs every 5 minutes (configured in `app/Console/Kernel.php`)

### 6. **Migration** (Database Schema)
**File**: `database/migrations/2025_12_08_120000_add_reverse_requested_at_to_transactions_tables.php`
- **Purpose**: Adds `reverse_requested_at` column to transaction tables
- **Tables Modified**:
  - `transactions`
  - `archeive_transactions`
  - `backup_transactions`

### 7. **Permission Command** (Setup)
**File**: `app/Console/Commands/CreateReverseTransactionsPermission.php`
- **Purpose**: Creates the "Reverse Transactions" permission
- **Command**: `php artisan permission:create-reverse-transactions`

### 8. **Routes** (Routing)
**File**: `routes/admin.php` (lines 58-64)
- **Route Group**: `transaction.reversal.*`
- **Prefix**: `/admin/transaction/reversal`

### 9. **Scheduler Configuration**
**File**: `app/Console/Kernel.php`
- **Line 92**: `$schedule->command('transactions:auto-reverse')->everyFiveMinutes();`
- **Purpose**: Automatically runs reversal command every 5 minutes

### 10. **Model Files** (Database Models)
- **File**: `app/Models/Transaction.php`
  - Must include `reverse_requested_at` in `$fillable` array
- **File**: `app/Models/ArcheiveTransaction.php`
  - Must include `reverse_requested_at` in `$fillable` array
- **File**: `app/Models/BackupTransaction.php`
  - Must include `reverse_requested_at` in `$fillable` array

---

## Configuration

### Environment Variables
Add to `.env` file:
```env
REVERSAL_WAIT_HOURS=6
```
- Default: 6 hours if not set
- Controls how long to wait before auto-reversing

---

## Setup Instructions

### Step 1: Run Migration
```bash
php artisan migrate
```
This adds `reverse_requested_at` column to all transaction tables.

### Step 2: Create Permission
```bash
php artisan permission:create-reverse-transactions
```
This creates the "Reverse Transactions" permission in the database.

### Step 3: Assign Permission
Assign the "Reverse Transactions" permission to admin roles/users who should have access.

### Step 4: Verify Scheduler
Ensure Laravel scheduler is running:
```bash
# Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 5: Test
1. Mark a successful transaction for reversal
2. Verify it appears in `/admin/transaction/reversal/list`
3. Wait 6 hours (or change `REVERSAL_WAIT_HOURS` to test faster)
4. Verify auto-reversal runs via scheduler

---

## Workflow

### Entry Points - Where to Mark Transaction for Reversal

The reversal process can be initiated from **two different pages**:

#### 1. **Transaction Search Page** (`/admin/searching/list`)
- **File**: `resources/views/admin/searching/list.blade.php`
- **DataTable**: `app/DataTables/Admin/SearchingDataTable.php`
- **Button Location**: "Detail" column in search results
- **Shows For**: Transactions with `status = 'success'` and `reverse_requested_at = null`
- **Permission Required**: "Reverse Transactions"

#### 2. **Transaction List Page** (`/admin/transaction/list`)
- **File**: `resources/views/admin/transaction/list.blade.php`
- **DataTable**: `app/DataTables/Admin/TransactionDataTable.php`
- **Button Location**: "Inquiry" column in transaction list
- **Shows For**: Transactions with `status = 'success'` and `reverse_requested_at = null`
- **Permission Required**: "Reverse Transactions"

### Mark Transaction for Reversal (Complete Flow)
1. **Admin navigates** to either:
   - Transaction Search page (`/admin/searching/list`)
   - Transaction List page (`/admin/transaction/list`)
2. **Finds a successful transaction** (`status = 'success'`)
3. **Clicks "Mark for Reversal" button** (warning button style)
4. **Confirmation dialog** appears: "Are you sure you want to mark this transaction for reversal? A 6-hour countdown will start."
5. **AJAX request** sent to `POST /admin/transaction/reversal/mark` with:
   - Transaction ID
   - Table type (`transactions`, `archeive_transactions`, or `backup_transactions`)
6. **Service method** `markForReversal()` is called:
   - Validates transaction exists
   - Checks status is 'success'
   - Sets `reverse_requested_at = now()`
   - Saves transaction
7. **Success response** returns with message
8. **Page reloads** to reflect changes
9. **Transaction appears** in "Pending Reversals" page (`/admin/transaction/reversal/list`)
10. **Countdown timer starts** showing time until auto-reversal

### Auto-Reversal Process
1. Scheduler runs `transactions:auto-reverse` every 5 minutes
2. Command finds transactions where:
   - `reverse_requested_at` is not null
   - `reverse_requested_at` + 6 hours <= now()
   - `status` = 'success'
3. Updates `status` to 'reverse'
4. Clears `reverse_requested_at`

### Cancel Reversal
1. Admin clicks "Cancel" button before 6-hour deadline
2. `reverse_requested_at` is set to null
3. `status` remains 'success'
4. Transaction removed from pending reversals

### Reverse Immediately
1. Admin clicks "Reverse Now" button
2. `status` immediately changed to 'reverse'
3. `reverse_requested_at` cleared
4. No waiting period

---

## Database Schema

### Column Added
```sql
reverse_requested_at TIMESTAMP NULL
```
- **Location**: After `status` column
- **Tables**: `transactions`, `archeive_transactions`, `backup_transactions`
- **Purpose**: Stores timestamp when reversal was requested

### Status Values
- `success` - Transaction is successful
- `reverse` - Transaction has been reversed
- Other statuses remain unchanged

---

## API Endpoints

### Mark for Reversal
```http
POST /admin/transaction/reversal/mark
Content-Type: application/json

{
    "id": 123,
    "table_type": "transactions" // optional
}
```

### Cancel Reversal
```http
POST /admin/transaction/reversal/cancel
Content-Type: application/json

{
    "id": 123,
    "table_type": "transactions" // optional
}
```

### Reverse Now
```http
POST /admin/transaction/reversal/reverse-now
Content-Type: application/json

{
    "id": 123,
    "table_type": "transactions" // optional
}
```

### Bulk Reverse
```http
POST /admin/transaction/reversal/bulk-reverse
Content-Type: application/json

{
    "ids": [123, 456, 789],
    "table_types": ["transactions", "archeive_transactions", "transactions"] // optional
}
```

---

## Key Features

1. **Multiple Entry Points**: Can mark for reversal from Transaction Search page OR Transaction List page
2. **Multi-Table Support**: Works with `transactions`, `archeive_transactions`, and `backup_transactions`
3. **Auto-Detection**: Automatically detects which table contains a transaction
4. **Countdown Timer**: Real-time countdown showing time until auto-reversal
5. **Bulk Operations**: Select and reverse/cancel multiple transactions at once
6. **Permission-Based**: Requires "Reverse Transactions" permission
7. **Logging**: All operations are logged for audit trail
8. **Configurable Wait Time**: Adjustable via environment variable
9. **Button Visibility**: "Mark for Reversal" button only shows for eligible transactions (success status, not already marked)
10. **Confirmation Dialogs**: All critical actions require user confirmation

---

## File Summary

| File | Type | Purpose |
|------|------|---------|
| `app/Services/TransactionReversalService.php` | Service | Core business logic |
| `app/Http/Controllers/Admin/TransactionReversalController.php` | Controller | HTTP endpoints |
| `app/DataTables/Admin/ReversalDataTable.php` | DataTable | Data formatting for pending reversals |
| `app/DataTables/Admin/SearchingDataTable.php` | DataTable | Adds "Mark for Reversal" button in search results (lines 27-57) |
| `app/DataTables/Admin/TransactionDataTable.php` | DataTable | Adds "Mark for Reversal" button in transaction list (lines 29-59) |
| `resources/views/admin/transaction/reversals.blade.php` | View | Admin UI for managing reversals |
| `resources/views/admin/searching/list.blade.php` | View | JavaScript handler for mark reversal button (lines 136-165) |
| `resources/views/admin/transaction/list.blade.php` | View | JavaScript handler for mark reversal button (lines 182-208) |
| `resources/views/admin/layout/include/sidebar.blade.php` | View | Sidebar menu item (lines 69-74) |
| `app/Console/Commands/AutoReverseTransactions.php` | Command | Scheduled task |
| `app/Console/Commands/CreateReverseTransactionsPermission.php` | Command | Setup permission |
| `database/migrations/2025_12_08_120000_add_reverse_requested_at_to_transactions_tables.php` | Migration | Database schema |
| `app/Console/Kernel.php` | Config | Scheduler setup |
| `routes/admin.php` | Routes | Route definitions |
| `app/Models/Transaction.php` | Model | Transaction model |
| `app/Models/ArcheiveTransaction.php` | Model | Archive model |
| `app/Models/BackupTransaction.php` | Model | Backup model |

---

## Testing Checklist

- [ ] Migration runs successfully
- [ ] Permission created
- [ ] Can mark transaction for reversal
- [ ] Pending reversals page displays correctly
- [ ] Countdown timer works
- [ ] Cancel reversal works
- [ ] Reverse now works
- [ ] Bulk operations work
- [ ] Auto-reversal runs after 6 hours
- [ ] Logs are created correctly
- [ ] Permission check works

---

## Troubleshooting

### Auto-reversal not running
- Check if scheduler is running: `php artisan schedule:list`
- Verify cron job is set up
- Check logs for errors

### Countdown timer not updating
- Check browser console for JavaScript errors
- Verify `updateCountdownTimers()` function is called

### Permission denied
- Run `php artisan permission:create-reverse-transactions`
- Assign permission to user/role

### Transaction not found
- Verify transaction exists in one of the three tables
- Check `getTableType()` method is working correctly

---

## Notes

- The module supports transactions across three tables (transactions, archive, backup)
- Reversal only works on transactions with `status = 'success'`
- Once reversed, status changes to `'reverse'` and cannot be undone
- The 6-hour wait period is configurable via `.env` file
- All operations are logged for audit purposes

