<?php

namespace App\Services;

use App\Data\Searching\TransactionSearchFilters;
use App\Models\ArcheiveTransaction;
use App\Models\BackupTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionSearchService
{
    /** @var class-string<Model> */
    private const SOURCES = [
        Transaction::class,
        ArcheiveTransaction::class,
        BackupTransaction::class,
    ];

    /**
     * UNION of filtered queries across live, archive, and backup tables.
     * Pagination and ordering remain handled by Yajra DataTables after UNION.
     */
    public function buildCombinedQuery(TransactionSearchFilters $filters): Builder
    {
        $queries = array_map(
            fn (string $modelClass) => $this->buildFilteredQuery($modelClass::query(), $filters),
            self::SOURCES
        );

        [$primary, $archive, $backup] = $queries;

        return $primary->union($archive)->union($backup);
    }

    /**
     * Apply shared search filters to a single transaction source query.
     */
    public function buildFilteredQuery(Builder $query, TransactionSearchFilters $filters): Builder
    {
        if ($filters->hasTransactionId()) {
            $query->where('transactionId', 'like', '%' . $filters->transactionId . '%');
        }

        if ($filters->hasPhone()) {
            $query->where('phone', 'like', '%' . $filters->phone . '%');
        }

        if ($filters->hasOrderId()) {
            $query->where('orderId', 'like', '%' . $filters->orderId . '%');
        }

        $this->applyExactFilters($query, $filters);

        return $query;
    }

    public function applyExactFilters(Builder $query, TransactionSearchFilters $filters): void
    {
        if ($filters->hasStartDate()) {
            try {
                $normalizedDate = Carbon::parse($filters->startDate)->toDateString();
                $query->whereDate('created_at', '=', $normalizedDate);
            } catch (\Throwable $e) {
                // Ignore invalid date input to preserve existing search flow.
            }
        }

        if ($filters->hasAmount()) {
            $query->where('amount', '=', $filters->amountMin);
        }
    }
}
