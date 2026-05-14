<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\{Transaction,ArcheiveTransaction,BackupTransaction,Payout,ArcheivePayout,Summary,Setting,Settlement,User,SurplusAmount,WalletTransfer};
use DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GeneralController extends Controller
{
    public function index(Request $request)
    {
        $url = 'https://marketmaven.com.pk/api/get-transactions';

        // Sending the request
        $response = Http::get($url);
        $data = $response->json();
        foreach($data['data'] as $item){
            $transaction = Transaction::create([
                'orderId' => $item['orderId'],
                'amount' => $item['amount'],
                'txn_ref_no' => $item['txn_ref_no'],
                'transactionId' => $item['transactionId'],
                'txn_type' => $item['txn_type'],
                'status' => $item['status'],
                'pp_code' => $item['pp_code'],
                'pp_message' => $item['pp_message'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
            ]);
        
        }
          
    }
    public function checkStatus(Request $request)
    {
        // Fetch transactions by orderId
        $order_details = Transaction::where('orderId', $request->orderId)->get();

        if ($order_details->isEmpty()) {
            $order_details = ArcheiveTransaction::where('orderId', $request->orderId)->get();
        }
        
        if ($order_details->isEmpty()) {
            $order_details = BackupTransaction::where('orderId', $request->orderId)->get();
        }
    
        // Find the first transaction with 'success' status
        $successful_transaction = $order_details->where('status', 'success')->first();
    
        // If no successful transaction is found, take the first transaction
        $transaction = $successful_transaction ?? $order_details->first();
    
        return response()->json(['order' => $transaction]);
    }
    public function checkPayoutStatus(Request $request)
    {
        // Fetch transactions by orderId
        $order_details = Payout::where('orderId', $request->orderId)->get();
        
        if ($order_details->isEmpty()) {
            $order_details = ArcheivePayout::where('orderId', $request->orderId)->get();
        }
        // Find the first transaction with 'success' status
        $successful_transaction = $order_details->where('status', 'success')->first();
    
        // If no successful transaction is found, take the first transaction
        $transaction = $successful_transaction ?? $order_details->first();
    
        return response()->json(['order' => $transaction]);
    }
    public function dashboardData(Request $request)
    {
        $user=User::where('email',$request->client_email)->first();
       
        
        $userId = $user->id;
    
        $payinSuccess=Transaction::where('user_id',$userId)
            ->where('status','success')
            ->whereDate('created_at',today())
            ->sum('amount');
        $payoutSuccess=Payout::where('user_id',$userId)
            ->where('status','success')
            ->whereDate('created_at',today())
            ->sum('amount');
        $prevBal=Settlement::where('user_id',$userId)
            ->whereDate('date',today()->subDay()->format('Y-m-d'))
            ->select('closing_bal')
            ->value('closing_bal');
        $prevUsdt=Settlement::where('user_id',$userId)
            ->whereDate('date',today()->format('Y-m-d'))
            ->select('usdt')
            ->value('usdt');
        $assignedAmount=Setting::where('user_id',$userId)->select('jazzcash','easypaisa','payout_balance')->first();
        $payin_fee=$user->payin_fee;
        $payout_fee=$user->payout_fee;
        // Calculation for unsettled amount
        $unSettledAmount= $prevBal + $payinSuccess - ($payinSuccess*$payin_fee + $payoutSuccess + $payoutSuccess*$payout_fee + $prevUsdt);
    
        return response()->json([
            'Previous Balance' => number_format($prevBal),
            'Payin' => number_format($payinSuccess),
            'Payout' => number_format($payoutSuccess),
            'JC' => number_format($assignedAmount->jazzcash ?? 0),
            'EP' => number_format($assignedAmount->easypaisa ?? 0),
            'Total' => number_format($assignedAmount->payout_balance ?? 0),
            'USDT' => number_format($prevUsdt),
            'Unsettled (After Fee)' => number_format($unSettledAmount),
        ]);
    }

    public function dashboardDataV1(Request $request)
    {
       
        $user = $request->user;
        
        $userId = $user->id;
    
        $payinSuccess=Transaction::where('user_id',$userId)
            ->where('status','success')
            ->whereDate('created_at',today())
            ->sum('amount');
        $payoutSuccess=Payout::where('user_id',$userId)
            ->where('status','success')
            ->whereDate('created_at',today())
            ->sum('amount');
        $prevBal=Settlement::where('user_id',$userId)
            ->whereDate('date',today()->subDay()->format('Y-m-d'))
            ->select('closing_bal')
            ->value('closing_bal');
        $prevUsdt=Settlement::where('user_id',$userId)
            ->whereDate('date',today()->format('Y-m-d'))
            ->select('usdt')
            ->value('usdt');
        $assignedAmount=Setting::where('user_id',$userId)->select('jazzcash','easypaisa','payout_balance')->first();
        $payin_fee=$user->payin_fee;
        $payout_fee=$user->payout_fee;
        // Calculation for unsettled amount
        $unSettledAmount= $prevBal + $payinSuccess - ($payinSuccess*$payin_fee + $payoutSuccess + $payoutSuccess*$payout_fee + $prevUsdt);
    
        return response()->json([
            'Previous Balance' => number_format($prevBal),
            'Payin' => number_format($payinSuccess),
            'Payout' => number_format($payoutSuccess),
            'JC' => number_format($assignedAmount->jazzcash ?? 0),
            'EP' => number_format($assignedAmount->easypaisa ?? 0),
            'Total' => number_format($assignedAmount->payout_balance ?? 0),
            'USDT' => number_format($prevUsdt),
            'Unsettled (After Fee)' => number_format($unSettledAmount),
        ]);
    }
    public function getPayinData()
    {
        $users = [3, 4, 6];
        $results = [];
        
        foreach ($users as $userId) {
            $todayPayin = DB::table('transactions')
                ->where('user_id', $userId)
                ->whereIn('status', ['success', 'reverse'])
                ->whereDate('created_at', Carbon::today())
                ->sum('amount');
        
            $todayTransReverse = DB::table('transactions')
                ->where('user_id', $userId)
                ->where('status', 'reverse')
                ->whereDate('updated_at', Carbon::today())
                ->sum('amount');
        
            $todayArcReverse = DB::table('archeive_transactions')
                ->where('user_id', $userId)
                ->where('status', 'reverse')
                ->whereDate('updated_at', Carbon::today())
                ->sum('amount');
        
            $todayBackReverse = DB::table('backup_transactions')
                ->where('user_id', $userId)
                ->where('status', 'reverse')
                ->whereDate('updated_at', Carbon::today())
                ->sum('amount');
        
            $todayReverse = $todayTransReverse + $todayArcReverse + $todayBackReverse;
        
            if ($userId == 3) {
                $todayPayinUserPiq   = $todayPayin;
                $todayReverseUserPiq = $todayReverse;
            } elseif ($userId == 4) {
                $todayPayinUserOk   = $todayPayin;
                $todayReverseUserOk = $todayReverse;
            // } elseif ($userId == 6) {
            //     $todayPayinUserPkn   = $todayPayin;
            //     $todayReverseUserPkn = $todayReverse;
            }
        }
        
        return [
            'today_payin_piq'   => $todayPayinUserPiq ?? 0,
            'today_reverse_piq' => $todayReverseUserPiq ?? 0,
            'today_payin_ok'   => $todayPayinUserOk ?? 0,
            'today_reverse_ok' => $todayReverseUserOk ?? 0,
            // 'today_payin_pkn'   => $todayPayinUserPkn ?? 0,
            // 'today_reverse_pkn' => $todayReverseUserPkn ?? 0,
        ];
        
        
    }
    
    public function getSettlementData()
    {
        $activeUserIds = User::where('user_role', 'Client')->where('active', 1)->pluck('id');
        $settlementData = Settlement::whereIn('user_id', $activeUserIds)
            ->whereDate('date', Carbon::today()->format('y-m-d'))
            ->get();
        $settingData=Setting::whereIn('user_id', $activeUserIds)->get();
        $surplusData=SurplusAmount::where('id', 1)->get();
        
        return [
            'settlements' => $settlementData,
            'settings'    => $settingData,
            'surplus'     => $surplusData,
        ];
    }
    public function addWalletAmount(Request $request)
    {
        // if($request->user_id == "2"){
        //     $userId = 4;
        // }
        // else{

        // }

        $userId = null;

        if ($request->from_store_name == "Monotech") {
            $userId = $request->user_id == "2" ? 4 : ($request->user_id == "4" ? 36 : null);
        } else {
            $userId = $request->user_id == "19" ? 4 : ($request->user_id == "4" ? 36 : null);
        }
        
        $trans_amount=$request->trans_amount * -1;

        WalletTransfer::create([
            'date'        => $request->date,
            'time'        => $request->time,
            'user_id'     => $userId,
            'req_id'      => $request->req_id,
            'store_name'  => $request->from_store_name,
            'trans_amount'=> $trans_amount,
        ]);

        $summary=Settlement::where('user_id',$userId)->whereDate('date', Carbon::today()->format('y-m-d'))->first();
        $summary->update([
            'wallet_transfer' => $summary->wallet_transfer + ($trans_amount),
            'settled' => $summary->settled + ($trans_amount),
        ]);

        return response()->json(['status' => 'success']);
    }
    public function getCocktailData(Request $request)
    {
        // $request->validate([
        //     'usdt'=>'required',
        // ]);
        $user=User::where('email',$request->client_email)->first();
        
        $item = Settlement::where('user_id',$user->id)->whereDate('date', Carbon::today()->format('y-m-d'))->first();
        

        if($request->wallet_transfer > 0 && $request->store_name != "None"){
            $request->validate([
                'store_name'=>'required',
                'wallet_transfer'=>'required',
            ]);
            $date = now()->format('Y-m-d');
            $time = now()->format('H:i:s');
            $req_id = 'REQ-' . now()->format('YmdHis') . '-' . Str::random(6);
            
            if($request->store_name == "Monotech"){
                $url = 'https://monotech.pk/api/add-wallet-transfer-amount';
            }else{
                $url = 'https://novapay.pk/api/add-wallet-transfer-amount';
            }
            $response = Http::timeout(10)->post($url, [
                'date'        => $date,
                'time'        => $time,
                'user_id'     => $item->user_id,
                'req_id'      => $req_id,
                'store_name'  => $request->store_name,
                'from_store_name' => "Khushi Connect",
                'trans_amount'=> $request->wallet_transfer,
            ]);
    
            $result = $response->json();

            if ($result['status'] == 'success') {

                WalletTransfer::create([
                    'date'        => now()->format('Y-m-d'),
                    'time'        => now()->format('H:i:s'),
                    'user_id'     => $item->user_id,
                    'req_id'      => 'REQ-' . now()->format('YmdHis') . '-' . Str::random(6),
                    'store_name'  => $request->store_name,
                    'trans_amount'=> $request->wallet_transfer,
                ]);

            }
        }
        if($request->store_name == "None"){
            WalletTransfer::create([
                'date'        => now()->format('Y-m-d'),
                'time'        => now()->format('H:i:s'),
                'user_id'     => $item->user_id,
                'req_id'      => 'REQ-' . now()->format('YmdHis') . '-' . Str::random(6),
                'store_name'  => $request->store_name,
                'trans_amount'=> $request->wallet_transfer,
            ]);
        }

        $totalUsdt = $item->usdt+$request->usdt;
        $todayWalletTrans = $item->wallet_transfer+$request->wallet_transfer;
        $item->usdt = $totalUsdt;
        $item->wallet_transfer = $todayWalletTrans;
        $item->settled = $item->settled+$totalUsdt+$todayWalletTrans;

        $item->save();

        return response()->json(['status' => 'success']);
    }
    public function addSurplusCocktail(Request $request)
    {
        $surplus=SurplusAmount::where('id','1')->first();
        $surplus->jazzcash=$surplus->jazzcash+$request->jazzcash  * 0.995;
        $surplus->easypaisa=$surplus->easypaisa+$request->easypaisa * 0.9925;
        $surplus->save();

        return response()->json(['status' => 'success']);
    }
    public function addCocktailSettlements()
    {
        $activeUserIds = User::where('user_role', 'Client')
            ->where('active', 1)
            ->pluck('id');

        $settlementData = Settlement::whereIn('user_id', $activeUserIds)
            ->whereBetween('date', ['2026-03-01', '2026-04-29'])
            ->get();

        return [
            'settlements' => $settlementData,
        ];
    }
}