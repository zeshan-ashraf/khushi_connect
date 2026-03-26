<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Payout,Transaction,User,Settlement, Setting, BackupTransaction, SurplusAmount};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today=today()->format('d-m-Y');
        $currentUser = Auth::user();
        $clients = User::where('user_role', 'Client')->where('active',1)->get();
        $month = Carbon::now()->month;
        $totalMonthlyAmount = DB::table('settlements')
            ->whereMonth('created_at', $month)
            ->sum('ep_payin');
        $data = [];
        
        foreach ($clients as $client) {
            $userId = $client->id;
            
            $prevBal=Settlement::where('user_id', $userId)->whereDate('date', today()->subDay())->value('closing_bal') ?? 0;
            $settlement=Settlement::where('user_id', $userId)->whereDate('date', today())->first();
            
            $epPayinAmount = $settlement->ep_payin;
            $jcPayinAmount = $settlement->jc_payin;
            $epPayoutAmount = $settlement->ep_payout;
            $jcPayoutAmount = $settlement->jc_payout;
            $reverseAmount = $settlement->reverse_amount ?? 0;
            // if($userId == 2 || $userId == 18){
                $payinSuccess= $epPayinAmount + $jcPayinAmount;
            // }else{
                // $payinSuccess= $jcPayinAmount;
            // }
            $payoutSuccess= $epPayoutAmount + $jcPayoutAmount;
            $prevUsdt= $settlement->usdt;
            $prevWalletTrans= $settlement->wallet_transfer;
            $payinFee=$client->payin_fee;
            $payoutFee=$client->payout_fee;
            //getUnsettlement
            // if ($userId == 2) {
            //     $payinSuccess = $epPayinAmount;
            // } 
            $unsettletdAmount=$prevBal + $payinSuccess - ($payinSuccess*$payinFee + $payoutSuccess + $payoutSuccess*$payoutFee + $prevUsdt + $reverseAmount +$prevWalletTrans);
            $assignedAmount=Setting::where('user_id',$userId)->select('jazzcash','easypaisa','payout_balance')->first();
            $balance= $unsettletdAmount - $assignedAmount->payout_balance ;
    
            $data[] = [ 
                'user' => $client,
                'prev_balance' => $prevBal,
                'jc_payin' => $jcPayinAmount,
                'ep_payin' => $epPayinAmount,
                'reverse_amount' => $reverseAmount,
                'total_payin' => $payinSuccess,
                'jc_payout' => $jcPayoutAmount,
                'ep_payout' => $epPayoutAmount,
                'total_payout' => $payoutSuccess,
                'prev_usdt' => $prevUsdt,
                'wallet_transfer' => $prevWalletTrans,
                'unsettled_amount' => $unsettletdAmount,
                'unsettled_amount_balance' => $balance,
                'assigned_amount' => Setting::where('user_id', $userId)->first(),
                'setting' => Setting::where('user_id', $userId)->first(),
                'set_id'=> $settlement->id,
            ];
        }
        $totals = [
            'prev_balance' => 0,
            'jc_payin' => 0,
            'ep_payin' => 0,
            'reverse_amount' => 0,
            'total_payin' => 0,
            'jc_payout' => 0,
            'ep_payout' => 0,
            'total_payout' => 0,
            'prev_usdt' => 0,
            'wallet_transfer' => 0,
            'unsettled_amount' => 0,
            'unsettled_amount_balance'=>0,
            'assigned_jc' => 0,
            'assigned_ep' => 0,
            'assigned_payout' => 0,
        ];
        
        foreach ($data as $item) {
            $totals['prev_balance'] += $item['prev_balance'];
            $totals['jc_payin'] += $item['jc_payin'];
            $totals['ep_payin'] += $item['ep_payin'];
            $totals['total_payin'] += $item['total_payin'];
            $totals['jc_payout'] += $item['jc_payout'];
            $totals['ep_payout'] += $item['ep_payout'];
            $totals['total_payout'] += $item['total_payout'];
            $totals['prev_usdt'] += $item['prev_usdt'];
            $totals['wallet_transfer'] += $item['wallet_transfer'];
            $totals['unsettled_amount'] += $item['unsettled_amount'];
            $totals['unsettled_amount_balance'] += $item['unsettled_amount_balance'];
            $totals['assigned_jc'] += $item['assigned_amount']->jazzcash ?? 0;
            $totals['assigned_ep'] += $item['assigned_amount']->easypaisa ?? 0;
            $totals['assigned_payout'] += $item['assigned_amount']->payout_balance ?? 0;
        }
        if (in_array($currentUser->user_role, ['Super Admin', 'Manager'])) {
            $users = User::where('user_role', 'Client')->where('active', 1)->get(); // All active users
        } else {
            $users = collect([$currentUser]); // Only auth user
        }
        $userStats = [];
        foreach ($users as $user) {
        
            $userStats[$user->id] = [
                'name' => $user->name,
                'success_rate' => round(srCount($user->id), 2),
                'jazzcash_pending' => Transaction::where('status', 'pending')->where('user_id', $user->id)->where('txn_type', 'jazzcash')->count(),
                'easypaisa_pending' => Transaction::where('status', 'pending')->where('user_id', $user->id)->where('txn_type', 'easypaisa')->count(),
            ];
        }
        $results = Settlement::where('user_id', 4)
            // ->orderBy('date', 'DESC')
            ->whereDate('date', Carbon::today())
            ->first();
        // $jcTopPendingOrder = Transaction::where([
        //     ['status', 'pending'],
        //     ['user_id', '10'],
        //     ['txn_type', 'jazzcash']
        // ])->count();
        
        // $epTopPendingOrder = Transaction::where([
        //     ['status', 'pending'],
        //     ['user_id', '10'],
        //     ['txn_type', 'easypaisa']
        // ])->count();
        
        
        $list = Setting::all();
        $surplusAmount=SurplusAmount::where('id','1')->first();
        return view('admin.index', get_defined_vars());

    }

    public function profile()
    {
        $user = auth()->user();
        return view('admin.security.profile',get_defined_vars());
    }
    public function accountSetting()
    {
        $user = auth()->user();
        return view('admin.security.password',get_defined_vars());
    }
    public function securityUpdate(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed','min:8','max:20']
        ]);
        User::where('id' , auth()->user()->id)->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.account.settings')->with('message','Updated Successfully!');
    }
    public function testing()
    {
        $transactions = Transaction::where('user_id',4)->get();
        foreach($transactions as $transaction){
            // Prepare the data for the HTTP request
            $data = [
                'orderId' => $transaction->orderId,
                'TID' => $transaction->transactionId,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
            ];
        
            // Make an HTTP request
            $response = Http::timeout(60)->post($transaction->url, $data);
        }
        
    }
}
