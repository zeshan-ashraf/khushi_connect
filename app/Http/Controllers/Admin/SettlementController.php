<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Transaction,Payout,Settlement};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Settlement']);
    }
    
    public function show($id)
    {
        $results = Settlement::where('user_id', $id)
            ->orderBy('date', 'DESC')
            ->get();

        // Convert settlement dates to plain strings
        $dates = $results->pluck('date')->map(function ($d) {
            return is_string($d) ? $d : $d->format('Y-m-d');
        })->toArray();

        // Fetch counts grouped by date
        $transactionCounts = Transaction::where('user_id', $id)
            ->whereIn('status', ['success', 'failed'])
            ->whereIn(DB::raw('DATE(created_at)'), $dates)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        // Attach counts
        foreach ($results as $summary) {
            $date = is_string($summary->date) ? $summary->date : $summary->date->format('Y-m-d');
            $summary->transaction_count = $transactionCounts[$date] ?? 0;
        }

        return view('admin.settlement.list', compact('results'));
    }


    

    public function modal(Request $request)
    {
        $id = $request->id;
        $item = DB::table('settlements')->where('id',$id)->first();
        $html = view('admin.settlement.modal',get_defined_vars())->render();
        return response()->json(['html'=>$html]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usdt'=>'required',
        ]);
        $item = Settlement::findOrFail($request->id);
        $totalUsdt = $item->usdt+$request->usdt;
        $totalWalletTrans = $item->wallet_transfer+$request->wallet_transfer;
        $totalWallet = $item->ep_payout+$request->ep_payout;
        $item->usdt = $totalUsdt;
        $item->wallet_transfer = $totalWalletTrans;
        $item->ep_payout = $totalWallet;
        $item->transfer_wallet = $totalWallet;
        $item->wallet_transfer = $totalWalletTrans;
        $item->settled = $item->settled+$totalUsdt+$totalWallet+$totalWalletTrans;
        $item->save();
        $msg = "Summary Updated Successfully!";
        return redirect()->back()->with('message',$msg);
    }
}