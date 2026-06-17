<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\SurplusAmount;
use App\Models\Transaction;
use App\Models\User;
use App\Service\StatusService;
use App\Support\MerchantCallback;
use Illuminate\Console\Command;

class EasyPaisaCheckTransactionStatus extends Command
{
    protected $signature = 'transactions:easypaisa-check-status';

    protected $description = 'Check status of pending transactions and update them.';

    protected $statusService;

    public function __construct(StatusService $statusService)
    {
        parent::__construct();
        $this->statusService = $statusService;
    }

    public function handle()
    {
        $minAgeMinutes = (int) config('easypaisa.cron_pending_min_age_minutes', 5);

        $list = Transaction::where('status', 'pending')
            ->where('txn_type', 'easypaisa')
            ->where('created_at', '<=', now()->subMinutes($minAgeMinutes))
            ->get();

        set_time_limit(0);

        if ($list->isNotEmpty()) {
            foreach ($list as $item) {
                $item->refresh();
                if ($item->status !== 'pending') {
                    continue;
                }

                $result = $this->statusService->process($item);
                $user = User::find($item->user_id);

                

                if (($result['transactionStatus'] ?? '') === 'PAID') {
                    $updated = Transaction::where('id', $item->id)
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'success',
                            'transactionId' => $result['transactionId'] ?? $result['msisdn'] ?? null,
                        ]);

                    if ($updated === 0) {
                        continue;
                    }

                    $item->refresh();

                    if ($user && $user->per_payin_fee) {
                        $this->applyEasypaisaBalance($item, $user);
                    }

                    MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_easypaisa_pending_success');
                } elseif (($result['transactionStatus'] ?? '') === 'FAILED') {
                    $updated = Transaction::where('id', $item->id)
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'failed',
                            'transactionId' => $result['transactionId'] ?? $result['msisdn'] ?? null,
                            'pp_code' => $result['errorCode'] ?? null,
                            'pp_message' => $result['errorReason'] ?? null,
                        ]);

                    if ($updated === 0) {
                        continue;
                    }

                    $item->refresh();

                    if (MerchantCallback::shouldNotifyFailedFromCron($item)) {
                        MerchantCallback::notifyPayin($item, $user, 60, null, 'cron_easypaisa_pending_failed');
                    }
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
            // $setting->easypaisa += $amount;
            $setting->payout_balance += $amount;
            $setting->save();

            // $surplus->easypaisa -= $amount;
            // $surplus->save();
        }
    }
}
