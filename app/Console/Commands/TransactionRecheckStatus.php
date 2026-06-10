<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use App\Service\StatusService;
use App\Support\MerchantCallback;
use Illuminate\Console\Command;

class TransactionRecheckStatus extends Command
{
    protected $signature = 'transactions:jazzcash-recheck-status';

    protected $description = 'Recheck status of failed transactions and update them.';

    protected $statusService;

    public function __construct(StatusService $statusService)
    {
        parent::__construct();
        $this->statusService = $statusService;
    }

    public function handle()
    {
        $list = Transaction::where('status', 'failed')
            ->where('pp_message', 'Transaction is Pending')
            ->where('pp_code', '157')
            ->where('txn_type', 'jazzcash')
            ->get();

        set_time_limit(0);

        if ($list->isNotEmpty()) {
            foreach ($list as $item) {
                $result = $this->statusService->process($item);
                $user = User::find($item->user_id);

                if ($result['pp_ResponseCode'] == '000' && $result['pp_PaymentResponseCode'] == '121') {
                    $item->update([
                        'status' => 'success',
                        'transactionId' => $result['pp_AuthCode'],
                        'pp_code' => $result['pp_ResponseCode'],
                        'pp_message' => $result['pp_ResponseMessage'],
                    ]);
                    $item->refresh();

                    MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_jazzcash_recheck_success');
                } elseif ($result['pp_PaymentResponseCode'] == '157') {
                    $item->update([
                        'status' => 'pending',
                        'transactionId' => $result['pp_AuthCode'],
                        'pp_code' => $result['pp_PaymentResponseCode'],
                        'pp_message' => $result['pp_PaymentResponseMessage'],
                    ]);
                } else {
                    $item->update([
                        'status' => 'failed',
                        'transactionId' => $result['pp_AuthCode'],
                        'pp_code' => $result['pp_PaymentResponseCode'],
                        'pp_message' => $result['pp_PaymentResponseMessage'],
                    ]);
                    $item->refresh();

                    MerchantCallback::notifyPayin($item, $user, 120, null, 'cron_jazzcash_recheck_failed');
                }
            }
        }

        $this->info('Pending transactions checked and updated.');
    }
}
