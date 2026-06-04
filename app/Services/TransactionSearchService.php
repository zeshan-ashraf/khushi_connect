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
     * Entry point: unique lookup (sequential, no UNION) or multi-row UNION search.
     * Pagination and ordering remain handled by Yajra DataTables.
     */
    public function buildCombinedQuery(TransactionSearchFilters $filters): Builder
    {
        if ($filters->hasUniqueIdentifier()) {
            return $this->findByUniqueIdentifiers($filters);
        }

        return $this->searchByFiltersUnion($filters);
    }

    /**
     * Sequential table scan; stops at first match. No UNION.
     */
    public function findByUniqueIdentifiers(TransactionSearchFilters $filters): Builder
    {
        foreach (self::SOURCES as $modelClass) {
            $record = $this->findFirstOnSource($modelClass, $filters);

            if ($record !== null) {
                return $modelClass::query()->whereKey($record->getKey());
            }
        }

        return $this->emptyQuery();
    }

    /**
     * UNION across all sources when no unique identifier is provided.
     */
    public function searchByFiltersUnion(TransactionSearchFilters $filters): Builder
    {
        $queries = array_map(
            fn (string $modelClass) => $this->buildUnionBranchQuery($modelClass::query(), $filters),
            self::SOURCES
        );

        [$primary, $archive, $backup] = $queries;

        return $primary->union($archive)->union($backup);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function findFirstOnSource(string $modelClass, TransactionSearchFilters $filters): ?Model
    {
        $record = $this->firstWithExactUniqueColumns($modelClass, $filters);

        if ($record !== null) {
            return $record;
        }

        return $this->firstWithPrefixUniqueColumns($modelClass, $filters);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function firstWithExactUniqueColumns(string $modelClass, TransactionSearchFilters $filters): ?Model
    {
        $query = $modelClass::query();
        $this->applyUniqueIdentifierExactMatch($query, $filters);
        $this->applyMultiRowFilters($query, $filters);

        return $query->limit(1)->first();
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function firstWithPrefixUniqueColumns(string $modelClass, TransactionSearchFilters $filters): ?Model
    {
        $query = $modelClass::query();
        $this->applyUniqueIdentifierPrefixMatch($query, $filters);
        $this->applyMultiRowFilters($query, $filters);

        return $query->limit(1)->first();
    }

    private function applyUniqueIdentifierExactMatch(Builder $query, TransactionSearchFilters $filters): void
    {
        if ($filters->hasOrderId()) {
            $query->where('orderId', $filters->orderId);
        }

        if ($filters->hasTransactionId()) {
            $this->applyTransactionReferenceExactMatch($query, $filters->transactionId);
        }
    }

    private function applyUniqueIdentifierPrefixMatch(Builder $query, TransactionSearchFilters $filters): void
    {
        if ($filters->hasOrderId()) {
            $query->where('orderId', 'like', $filters->orderId . '%');
        }

        if ($filters->hasTransactionId()) {
            $this->applyTransactionReferencePrefixMatch($query, $filters->transactionId);
        }
    }

    /**
     * "Transaction Id" form input may be stored as transactionId (auth code) or txn_ref_no (gateway ref).
     */
    private function applyTransactionReferenceExactMatch(Builder $query, string $value): void
    {
        $query->where(function (Builder $inner) use ($value) {
            $inner->where('transactionId', $value)
                ->orWhere('txn_ref_no', $value);
        });
    }

    private function applyTransactionReferencePrefixMatch(Builder $query, string $value): void
    {
        $query->where(function (Builder $inner) use ($value) {
            $inner->where('transactionId', 'like', $value . '%')
                ->orWhere('txn_ref_no', 'like', $value . '%');
        });
    }

    /**
     * Filters that may return multiple rows (phone, amount, date).
     */
    private function applyMultiRowFilters(Builder $query, TransactionSearchFilters $filters): void
    {
        if ($filters->hasPhone()) {
            $query->where('phone', 'like', '%' . $filters->phone . '%');
        }

        $this->applyExactFilters($query, $filters);
    }

    /**
     * UNION branch: multi-row filters only (unique columns handled elsewhere).
     */
    private function buildUnionBranchQuery(Builder $query, TransactionSearchFilters $filters): Builder
    {
        $this->applyMultiRowFilters($query, $filters);

        return $query;
    }

    public function applyExactFilters(Builder $query, TransactionSearchFilters $filters): void
    {
        if ($filters->hasStartDate()) {
            try {
                $date = Carbon::parse($filters->startDate);
                $query->where('created_at', '>=', $date->copy()->startOfDay())
                    ->where('created_at', '<=', $date->copy()->endOfDay());
            } catch (\Throwable $e) {
                // Ignore invalid date input to preserve existing search flow.
            }
        }

        if ($filters->hasAmount()) {
            $query->where('amount', '=', $filters->amountMin);
        }
    }

    private function emptyQuery(): Builder
    {
        return Transaction::query()->whereRaw('1 = 0');
    }
}
