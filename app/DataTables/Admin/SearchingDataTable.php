<?php

namespace App\DataTables\Admin;

use App\Data\Searching\TransactionSearchFilters;
use App\Services\TransactionSearchService;
use App\Support\PayinCallbackTracker;
use Yajra\DataTables\Services\DataTable;

class SearchingDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('user_id', function ($query) {
                return $query->user ? $query->user->name : 'N/A';
            })
            ->editColumn('status', function ($query) {
                $reason = $query->pp_message;
                $type = $query->status;

                return view('admin.transaction.badge', compact('reason', 'type'))->render();
            })
            ->editColumn('callback_sent', function ($query) {
                return view('admin.transaction.callback_badge', PayinCallbackTracker::badgeData($query));
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at ? $query->created_at->format('d-m-y H:i:s') : 'N/A';
            })
            ->editColumn('amount', function ($query) {
                return $query->amount;
            })
            ->editColumn('txn_type', function ($query) {
                return $this->formatNetwork($query->txn_type ?? null);
            })
            ->editColumn('detail', function ($query) {
                $user = auth()->user();
                $buttons = '<div class="d-flex flex-wrap justify-content-center align-items-center gap-1 searching-action-btns">';
                $buttons .= '<a href="' . route('admin.searching.callback.send', $query->id) . '" class="btn btn-success btn-sm">Send Callback</a>';
                $buttons .= '<a href="' . route('admin.jazzcash.status-inquiry', ['id' => $query->txn_ref_no, 'type' => $query->txn_type]) . '" class="btn btn-primary btn-sm">Inquiry</a>';

                // if ($user && method_exists($user, 'can') && $user->can('Reverse Transactions') && $query->status == 'success') {
                //     $reverseRequested = isset($query->reverse_requested_at) ? $query->reverse_requested_at : null;

                //     if (!$reverseRequested) {
                //         $tableType = 'transactions';

                //         $buttons .= '<button class="btn btn-warning btn-sm mark-for-reversal-btn" data-id="' . $query->id . '" data-table-type="' . $tableType . '">Mark for Reversal</button>';
                //     }
                // }

                if ($user->user_role == 'Super Admin' && $query->status == 'success') {

                    $buttons .= '
                        <button type="button"
                                class="btn btn-warning btn-sm reverse-btn"
                                data-id="' . $query->id . '">
                            Reverse
                        </button>
                    ';
                }

                $buttons .= '</div>';

                return $buttons;
            })->rawColumns(['detail']);
    }

    public function query()
    {
        $filters = TransactionSearchFilters::fromRequest(request());

        $combinedQuery = app(TransactionSearchService::class)->buildCombinedQuery($filters);

        return $this->applyScopes($combinedQuery);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('dataTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"row align-items-center"<"col-md-2" l><"col-md-6" B><"col-md-4"f>><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" i><"col-md-6" p>><"clear">')
            ->parameters([
                'buttons' => [
                    'excel',
                ],
                'processing' => true,
                'autoWidth' => false,
                'lengthChange' => false,
                'searching' => false,
                'drawCallback' => 'function () {
                        }',
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            ['data' => 'orderId', 'name' => 'orderId', 'title' => 'Order Id', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'user_id', 'name' => 'user_id', 'title' => 'Client', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'transactionId', 'name' => 'transactionId', 'title' => 'Trans Id', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'phone', 'name' => 'phone', 'title' => 'Phone No', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'txn_ref_no', 'name' => 'txn_ref_no', 'title' => 'Trans Ref No', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'txn_type', 'name' => 'txn_type', 'title' => 'Network', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'callback_sent', 'name' => 'callback_sent', 'title' => 'Callback', 'orderable' => false, 'searchable' => false, 'width' => 30],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created at', 'orderable' => true, 'searchable' => true, 'width' => 30],
            ['data' => 'detail', 'name' => 'detail', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'width' => '15%'],
            // ['data' => 'reverse', 'name' => 'reverse', 'title' => 'Change Status', 'orderable' => false, 'searchable' => false, 'width' => '15%'],

        ];
    }

    private function formatNetwork(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'jazzcash' => 'JC',
            'easypaisa' => 'EP',
            default => $value !== null && $value !== '' ? (string) $value : '-',
        };
    }

    protected function filename(): string
    {
        return 'Export_' . date('YmdHis');
    }

    protected function sheetName(): string
    {
        return 'Yearly Report';
    }
}
