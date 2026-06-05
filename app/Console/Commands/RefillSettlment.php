<?php

namespace App\Console\Commands;

use App\Models\Settlement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class RefillSettlment extends Command
{
    private const ALLOWED_PAYIN_TABLES = [
        'transactions',
        'archeive_transactions',
        'backup_transactions',
    ];

    protected $signature = 'refillsettlment
                            {userId : Client user ID}
                            {date : Settlement date (Y-m-d)}
                            {payinTable : Table for JC/EP payin sums (transactions|archeive_transactions|backup_transactions)}';

    protected $description = 'Create or recalculate a missing settlement record for a user and date';

    public function handle(): int
    {
        try {
            $user = $this->resolveUser((int) $this->argument('userId'));
            $settlementDate = $this->resolveDate((string) $this->argument('date'));
            $payinTable = $this->resolvePayinTable((string) $this->argument('payinTable'));
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $dateString = $settlementDate->toDateString();
        $previousDateString = $settlementDate->copy()->subDay()->toDateString();

        $existing = Settlement::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $dateString)
            ->first();

        $preClosingBal = (float) (DB::table('settlements')
            ->where('user_id', $user->id)
            ->whereDate('date', $previousDateString)
            ->value('closing_bal') ?? 0);

        $dayUsdt = (float) ($existing?->usdt ?? DB::table('settlements')
            ->where('user_id', $user->id)
            ->whereDate('date', $dateString)
            ->value('usdt') ?? 0);

        $dayWalletTrans = (float) ($existing?->wallet_transfer ?? DB::table('settlements')
            ->where('user_id', $user->id)
            ->whereDate('date', $dateString)
            ->value('wallet_transfer') ?? 0);

        $transactionSumJC = $this->sumPayin($payinTable, $user->id, $settlementDate, 'jazzcash');
        $transactionSumEP = $this->sumPayin($payinTable, $user->id, $settlementDate, 'easypaisa');

        $transactionReverse = $this->sumReverse('transactions', $user->id, $settlementDate);
        $archiveReverse = $this->sumReverse('archeive_transactions', $user->id, $settlementDate);
        $backupReverse = $this->sumReverse('backup_transactions', $user->id, $settlementDate);

        $totalReverseAmount = $transactionReverse + $archiveReverse + $backupReverse;
        $transactionReverseHalf = (string) $user->id === '4'
            ? $totalReverseAmount * 0.5
            : $totalReverseAmount;

        $payoutSumJC = $this->sumPayout($user->id, $settlementDate, 'jazzcash');
        $payoutSumEP = $this->resolveEasypaisaPayoutSum($user, $settlementDate);

        $payinFeeJC = (float) $user->payin_fee;
        $payinFeeEP = (float) $user->payin_ep_fee;
        $payoutFeeJC = (float) $user->payout_fee;
        $payoutFeeEP = (float) $user->payout_ep_fee;

        $opCln = ($transactionSumJC + $transactionSumEP) * 0.015
            + ($payoutSumJC + $payoutSumEP) * 0.0075
            + $transactionReverseHalf;

        $revCln = ($transactionSumJC * $payinFeeJC + $transactionSumEP * $payinFeeEP)
            + ($payoutSumJC * $payoutFeeJC + $payoutSumEP * $payoutFeeEP)
            - $opCln;

        $settleAmount = $payoutSumJC + $payoutSumEP
            + ($payoutSumJC * $payoutFeeJC)
            + ($payoutSumEP * $payoutFeeEP)
            + $dayUsdt
            + $dayWalletTrans;

        if ((string) $user->id === '14') {
            $payinBal = $payoutSumEP + ($payoutSumEP * 0.0075);
            $closingBal = $preClosingBal + $payinBal - $dayUsdt;
        } else {
            $payinBal = $preClosingBal + $transactionSumJC + $transactionSumEP
                - ($transactionSumJC * $payinFeeJC)
                - ($transactionSumEP * $payinFeeEP)
                - $transactionReverseHalf;
            $closingBal = $payinBal - $settleAmount;
        }

        $payload = [
            'date' => $dateString,
            'user_id' => $user->id,
            'opening_bal' => $preClosingBal,
            'jc_payin' => $transactionSumJC,
            'ep_payin' => $transactionSumEP,
            'jc_payin_fee' => $transactionSumJC * $payinFeeJC,
            'ep_payin_fee' => $transactionSumEP * $payinFeeEP,
            'reverse_amount' => $transactionReverseHalf,
            'payin_bal' => $payinBal,
            'jc_payout' => $payoutSumJC,
            'ep_payout' => $payoutSumEP,
            'jc_payout_fee' => $payoutSumJC * $payoutFeeJC,
            'ep_payout_fee' => $payoutSumEP * $payoutFeeEP,
            'op_cln' => $opCln,
            'rev_cln' => $revCln,
            'usdt' => $dayUsdt,
            'wallet_transfer' => $dayWalletTrans,
            'settled' => $settleAmount,
            'closing_bal' => $closingBal,
        ];

        if ($existing) {
            $existing->update($payload);
            $action = 'updated';
        } else {
            Settlement::create($payload);
            $action = 'created';
        }

        $this->info("Settlement {$action} for user {$user->id} on {$dateString} using {$payinTable}.");
        $this->table(
            ['Field', 'Value'],
            collect($payload)->map(fn ($value, $key) => [$key, $value])->values()->all()
        );

        if ($preClosingBal === 0.0 && ! DB::table('settlements')
            ->where('user_id', $user->id)
            ->whereDate('date', $previousDateString)
            ->exists()) {
            $this->warn("No previous-day settlement found for {$previousDateString}; opening_bal set to 0.");
        }

        return self::SUCCESS;
    }

    private function resolveUser(int $userId): User
    {
        $user = User::query()->find($userId);

        if (! $user) {
            throw new InvalidArgumentException("User {$userId} not found.");
        }

        if ($user->user_role !== 'Client') {
            throw new InvalidArgumentException("User {$userId} is not a Client account.");
        }

        return $user;
    }

    private function resolveDate(string $date): Carbon
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException('Date must be in Y-m-d format, e.g. 2026-05-04.');
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            throw new InvalidArgumentException("Invalid date: {$date}");
        }
    }

    private function resolvePayinTable(string $payinTable): string
    {
        $payinTable = trim($payinTable);

        if (! in_array($payinTable, self::ALLOWED_PAYIN_TABLES, true)) {
            throw new InvalidArgumentException(
                'Payin table must be one of: '.implode(', ', self::ALLOWED_PAYIN_TABLES)
            );
        }

        return $payinTable;
    }

    private function sumPayin(string $table, int $userId, Carbon $date, string $txnType): float
    {
        return (float) DB::table($table)
            ->where('user_id', $userId)
            ->whereIn('status', ['success', 'reverse'])
            ->where('txn_type', $txnType)
            ->whereDate('created_at', $date)
            ->sum('amount');
    }

    private function sumReverse(string $table, int $userId, Carbon $date): float
    {
        return (float) DB::table($table)
            ->where('user_id', $userId)
            ->where('status', 'reverse')
            ->whereDate('updated_at', $date)
            ->sum('amount');
    }

    private function sumPayout(int $userId, Carbon $date, string $transactionType): float
    {
        return (float) DB::table('payouts')
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->where('transaction_type', $transactionType)
            ->whereDate('created_at', $date)
            ->sum('amount');
    }

    private function resolveEasypaisaPayoutSum(User $user, Carbon $date): float
    {
        if ((string) $user->id === '14' && $date->isToday()) {
            try {
                $response = Http::get('https://novapay.pk/api/get-nova-payout');
                $data = $response->json();

                if (is_array($data) && array_key_exists('today_ok_ep_payout', $data)) {
                    return (float) $data['today_ok_ep_payout'];
                }
            } catch (\Throwable $e) {
                $this->warn('Nova payout API failed; falling back to payouts table. '.$e->getMessage());
            }
        }

        return $this->sumPayout($user->id, $date, 'easypaisa');
    }
}
