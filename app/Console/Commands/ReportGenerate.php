<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{Settlement,SurplusAmount,TempAmountPayout,User};
use Illuminate\Support\Facades\Http;

class ReportGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily report for users';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $users=User::where('user_role','Client')->where('active',1)->get();
        $today = Carbon::today();
        foreach ($users as $user) {
            $sumamry= Settlement::where('user_id',$user->id)->whereDate('date', Carbon::today()->format('y-m-d'))->first();
            if($sumamry){
                // Get yesterday's closing balance
                $preClosingBal = DB::table('settlements')
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::today()->subDay(1)->format('y-m-d'))
                    ->value('closing_bal');
                
                $todayUsdt = DB::table('settlements')
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::today()->format('y-m-d'))
                    ->value('usdt');

                $todayWalletTrans = DB::table('settlements')
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::today()->format('y-m-d'))
                    ->value('wallet_transfer');
                
                // Sum of successful transaction amounts
                $transactionSumJC = DB::table('transactions')
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['success', 'reverse'])
                    ->where('txn_type', 'jazzcash')
                    ->whereDate('created_at', Carbon::today())
                    ->sum('amount');

                $transactionReverse = DB::table('transactions')
                    ->where('user_id', $user->id)
                    ->where('status', 'reverse')
                    ->whereDate('updated_at', Carbon::today())
                    ->sum('amount');

                $archiveReverse = DB::table('archeive_transactions')
                    ->where('user_id', $user->id)
                    ->where('status', 'reverse')
                    ->whereDate('updated_at', Carbon::today())
                    ->sum('amount');

                $backupReverse = DB::table('backup_transactions')
                    ->where('user_id', $user->id)
                    ->where('status', 'reverse')
                    ->whereDate('updated_at', Carbon::today())
                    ->sum('amount');

                $totalReverseAmount = $transactionReverse + $archiveReverse + $backupReverse;
                // if($user->id == "4"){
                //     $transactionReverseHalf = $totalReverseAmount * 0.5;
                // } else{
                    $transactionReverseHalf = $totalReverseAmount;
                // }
                $transactionSumEP = DB::table('transactions')
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['success', 'reverse'])
                    ->where('txn_type', 'easypaisa')
                    ->whereDate('created_at', Carbon::today())
                    ->sum('amount');
                
                // Sum of successful payout amounts
                if($user->id == "15"){
                    $payoutSumJC = DB::table('payouts')
                        // ->where('user_id', $user->id)
                        ->where('status', 'success')
                        ->where('transaction_type', 'jazzcash')
                        ->where('api_type', 'ZP')
                        ->whereDate('created_at', Carbon::today())
                        ->sum('amount');

                    // $payoutSumEP = DB::table('payouts')
                    //     // ->where('user_id', $user->id)
                    //     ->where('status', 'success')
                    //     ->where('transaction_type', 'easypaisa')
                    //     ->where('api_type', 'ZP')
                    //     ->whereDate('created_at', Carbon::today())
                    //     ->sum('amount');
                }
                else{
                    $payoutSumJC = DB::table('payouts')
                        ->where('user_id', $user->id)
                        ->where('status', 'success')
                        ->where('transaction_type', 'jazzcash')
                        ->whereDate('created_at', Carbon::today())
                        ->sum('amount');       
                }
                if ($user->id == "14") {
                    $url = 'https://novapay.pk/api/get-nova-payout';
                    $response = Http::get($url);
                    $data = $response->json();
                    $payoutSumEP = $data['today_ok_ep_payout'];
                } else {
                    $payoutSumEP = DB::table('payouts')
                        ->where('user_id', $user->id)
                        ->where('status', 'success')
                        ->where('transaction_type', 'easypaisa')
                        ->whereDate('created_at', Carbon::today())
                        ->sum('amount');
                }
            
                // $payoutSumEP = $sumamry->ep_payout;
                
                $payinFeeJC = $user->payin_fee;
                $payinFeeEP = $user->payin_ep_fee;
                $PayoutFeeJC = $user->payout_fee;
                $PayoutFeeEP = $user->payout_ep_fee;
            
                $op_cln=($transactionSumJC + $transactionSumEP) * 0.015 + ($payoutSumJC + $payoutSumEP) * 0.0075 +  $transactionReverseHalf;

                $rev_cln=($transactionSumJC * $payinFeeJC + $transactionSumEP * $payinFeeEP)  + ($payoutSumJC * $PayoutFeeJC + $payoutSumEP * $PayoutFeeEP) -  $op_cln;  
                $settleAmount = $payoutSumJC + $payoutSumEP + ($payoutSumJC * $PayoutFeeJC) + ($payoutSumEP * $PayoutFeeEP) + $todayUsdt +$todayWalletTrans;
                if($user->id == "15"){
                    $op_cln = 0;
                    $rev_cln = 0;
                    $settleAmount = 0;
                    $payinBal = 0;
                    $closingBal=0;
                } else {

                    // Calculate balances
                    $payinBal = $preClosingBal + $transactionSumJC + $transactionSumEP - ($transactionSumJC * $payinFeeJC) - ($transactionSumEP * $payinFeeEP) - $transactionReverseHalf;
                    $closingBal=$payinBal - $settleAmount;
                }
                // $payinBal = $closingBal + $transactionSumEP - ($transactionSumEP * $payinFeeEP) - $transactionReverseHalf;
            

                
                // Create a summary for the user
                $sumamry->update([
                    'date' => Carbon::today()->format('y-m-d'),
                    'user_id' => $user->id,
                    'opening_bal'  => $preClosingBal,
                    'jc_payin' => $transactionSumJC,
                    'ep_payin' => $transactionSumEP,
                    'jc_payin_fee' => $transactionSumJC * $payinFeeJC,
                    'ep_payin_fee' => $transactionSumEP * $payinFeeEP,
                    'reverse_amount' =>$transactionReverseHalf,
                    'payin_bal' => $payinBal,
                    'jc_payout' => $payoutSumJC,
                    'ep_payout' => $payoutSumEP,
                    'jc_payout_fee' => $payoutSumJC * $PayoutFeeJC,
                    'op_cln' => $op_cln,
                    'rev_cln' => $rev_cln,
                    'ep_payout_fee' => $payoutSumEP * $PayoutFeeEP,
                    'usdt' => $sumamry->usdt,
                    'wallet_transfer' => $sumamry->wallet_transfer,
                    'settled' => $settleAmount,
                    'closing_bal' => $closingBal,
                ]);
                
            }
            else{
                Settlement::create([
                    'date' => Carbon::today()->format('y-m-d'),
                    'user_id' => $user->id,
                    'opening_bal' => '0',
                    'jc_payin' => '0',
                    'ep_payin' => '0',
                    'jc_payin_fee' => '0',
                    'ep_payin_fee' => '0',
                    'payin_bal' => '0',
                    'jc_payout' => '0',
                    'ep_payout' => '0',
                    'jc_payout_fee' => '0',
                    'ep_payout_fee' => '0',
                    'op_cln' => '0',
                    'rev_cln' => '0',
                    'usdt' => '0',
                    'wallet_transfer' => '0',
                    'settled' => '0',
                    'closing_bal' => '0',
                ]);
                TempAmountPayout::query()->update([
                    'jc_amount' => 0,
                    'ep_amount' => 0,
                ]);
            }
        }
        $this->info('Daily report generated successfully.');
    }
}
