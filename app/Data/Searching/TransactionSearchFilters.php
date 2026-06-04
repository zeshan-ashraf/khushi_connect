<?php

namespace App\Data\Searching;

use Illuminate\Http\Request;

/**
 * Parsed filters for payin search (manual form + DataTables AJAX).
 */
final class TransactionSearchFilters
{
    public function __construct(
        public readonly ?string $orderId = null,
        public readonly ?string $phone = null,
        public readonly ?string $transactionId = null,
        public readonly mixed $amountMin = null,
        public readonly ?string $startDate = null,
        public readonly ?string $globalSearch = null,
        public readonly ?string $dataTableOrderColumn = null,
        public readonly ?string $dataTableOrderDirection = null,
        public readonly ?int $dataTableDraw = null,
        public readonly ?int $dataTableStart = null,
        public readonly ?int $dataTableLength = null,
        /** @var array<string, string> column name => search value */
        public readonly array $columnSearches = [],
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $columns = $request->input('columns', []);
        $columnSearches = [];

        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (! is_array($column)) {
                    continue;
                }

                $name = $column['name'] ?? $column['data'] ?? null;
                $value = $column['search']['value'] ?? null;

                if ($name && self::normalizeString($value) !== null) {
                    $columnSearches[(string) $name] = (string) self::normalizeString($value);
                }
            }
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumnName = null;

        if ($orderColumnIndex !== null && isset($columns[$orderColumnIndex])) {
            $orderColumn = $columns[$orderColumnIndex];
            $orderColumnName = is_array($orderColumn)
                ? ($orderColumn['name'] ?? $orderColumn['data'] ?? null)
                : null;
        }

        return new self(
            orderId: self::normalizeString($request->input('order_id')),
            phone: self::normalizeString($request->input('phone')),
            transactionId: self::normalizeString($request->input('transaction_Id')),
            amountMin: $request->input('amount_min'),
            startDate: self::normalizeString($request->input('start_date')),
            globalSearch: self::normalizeString(data_get($request->all(), 'search.value')),
            dataTableOrderColumn: $orderColumnName ? (string) $orderColumnName : null,
            dataTableOrderDirection: self::normalizeString($request->input('order.0.dir')),
            dataTableDraw: $request->has('draw') ? (int) $request->input('draw') : null,
            dataTableStart: $request->has('start') ? (int) $request->input('start') : null,
            dataTableLength: $request->has('length') ? (int) $request->input('length') : null,
            columnSearches: $columnSearches,
        );
    }

    public function hasOrderId(): bool
    {
        return $this->orderId !== null;
    }

    public function hasPhone(): bool
    {
        return $this->phone !== null;
    }

    public function hasTransactionId(): bool
    {
        return $this->transactionId !== null;
    }

    public function hasStartDate(): bool
    {
        return $this->startDate !== null;
    }

    public function hasAmount(): bool
    {
        return $this->amountMin !== null && $this->amountMin !== '';
    }

    public function hasGlobalSearch(): bool
    {
        return $this->globalSearch !== null;
    }

    /** orderId or transactionId — eligible for sequential early-exit lookup. */
    public function hasUniqueIdentifier(): bool
    {
        return $this->hasOrderId() || $this->hasTransactionId();
    }

    /** phone, amount, date, global search — require scanning all sources. */
    public function hasMultiRowFilters(): bool
    {
        return $this->hasPhone()
            || $this->hasStartDate()
            || $this->hasAmount()
            || $this->hasGlobalSearch()
            || $this->columnSearches !== [];
    }

    public function hasManualOrColumnFilters(): bool
    {
        return $this->hasUniqueIdentifier() || $this->hasMultiRowFilters();
    }

    private static function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
