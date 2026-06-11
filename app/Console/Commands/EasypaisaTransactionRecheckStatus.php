<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\SurplusAmount;
use App\Models\Transaction;
use App\Models\User;
use App\Service\StatusService;
use App\Support\MerchantCallback;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EasypaisaTransactionRecheckStatus extends Command
{
    protected $signature = 'transactions:easypaisa-recheck-status';

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
            ->where('pp_message', 'INVALID ORDER ID')
            ->where('pp_code', '0003')
            ->where('txn_type', 'easypaisa')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->get();

        set_time_limit(0);

        if ($list->isNotEmpty()) {
            foreach ($list as $item) {
                $result = $this->statusService->process($item);
                $user = User::find($item->user_id);

                if ($result['responseCode'] == '0000') {
                    if ($result['transactionStatus'] == 'PAID') {
                        $item->update([
                            'status' => 'success',
                            'transactionId' => $result['transactionId'] ?? $result['msisdn'] ?? null,
                        ]);
                        $item->refresh();

                        if ($user && $user->per_payin_fee) {
                            $this->applyEasypaisaBalance($item, $user);
                        }

                        MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_easypaisa_recheck_success');
                    } elseif ($result['transactionStatus'] == 'FAILED') {
                        $item->update([
                            'status' => 'failed',
                            'transactionId' => $result['transactionId'] ?? $result['msisdn'] ?? null,
                            'pp_code' => $result['errorCode'] ?? null,
                            'pp_message' => $result['errorReason'] ?? null,
                        ]);
                        $item->refresh();

                        MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_easypaisa_recheck_failed');
                    }
                } elseif ($result['responseCode'] == '0003') {
                    $item->update([
                        'status' => 'failed',
                        'pp_code' => $result['responseCode'],
                        'pp_message' => $result['responseDesc'],
                    ]);
                    $item->refresh();

                    MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_easypaisa_recheck_failed_0003');
                }
            }
        }

        $this->info('Pending transactions checked and updated.');
    }

    private function applyEasypaisaBalance(Transaction $item, User $user): void
    {
        $amount = $item->amount * $user->per_payin_fee;
        $surplus = SurplusAmount::find(1);
        $setting = Setting::where('user_id', $item->user_id)->first();

        if ($setting && $surplus && $setting->auto == 1) {
            $setting->easypaisa += $amount;
            $setting->payout_balance += $amount;
            $setting->save();

            // $surplus->easypaisa -= $amount;
            // $surplus->save();
        }
    }
}
