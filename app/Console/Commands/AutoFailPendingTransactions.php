<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\User;
use App\Support\MerchantCallback;
use Carbon\Carbon;

class AutoFailPendingTransactions extends Command
{
    protected $signature = 'transactions:auto-fail';
    protected $description = 'Mark pending transactions as failed after 30 minutes';

    public function handle()
    {
        $cutoffTime = Carbon::now()->subMinutes(45);

        $items = Transaction::where('status', 'pending')
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        $count = 0;
        foreach ($items as $item) {
            $item->update([
                'status' => 'failed',
                'pp_code' => '999',
                'pp_message' => 'Auto-failed after 30 minutes',
            ]);
            $item->refresh();

            MerchantCallback::notifyPayin($item, User::find($item->user_id), 60, null, 'cron_auto_fail');
            $count++;
        }

        $this->info("Updated $count transaction(s) to failed.");
    }
}
