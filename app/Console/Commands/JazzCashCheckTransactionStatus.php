<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\SurplusAmount;
use App\Models\Transaction;
use App\Models\User;
use App\Service\StatusService;
use App\Support\MerchantCallback;
use Illuminate\Console\Command;

class JazzCashCheckTransactionStatus extends Command
{
    protected $signature = 'transactions:jazzcash-check-status';

    protected $description = 'Check status of pending transactions and update them.';

    protected $statusService;

    public function __construct(StatusService $statusService)
    {
        parent::__construct();
        $this->statusService = $statusService;
    }

    public function handle()
    {
        $list = Transaction::where('status', 'pending')->where('txn_type', 'jazzcash')->get();

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

                    if ($user && $user->per_payin_fee) {
                        $this->applyJazzcashBalance($item, $user);
                    }

                    MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_jazzcash_pending_success');
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

                    if (MerchantCallback::shouldNotifyFailedFromCron($item)) {
                        MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_jazzcash_pending_failed');
                    }
                }
            }
        }

        $this->info('Pending transactions checked and updated.');
    }

    private function applyJazzcashBalance(Transaction $item, User $user): void
    {
        $amount = $item->amount * $user->per_payin_fee;
        $surplus = SurplusAmount::find(1);
        $setting = Setting::where('user_id', $item->user_id)->first();

        if ($setting && $surplus && $setting->auto == 1) {
            $setting->jazzcash += $amount;
            $setting->payout_balance += $amount;
            $setting->save();

            // $surplus->jazzcash -= $amount;
            // $surplus->save();
        }
    }
}
