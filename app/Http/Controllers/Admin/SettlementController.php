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
        
        foreach ($results as $summary) {
            $date = $summary->date; // Use the date as is
            $transactionCount = Transaction::where('user_id', $id)
                ->whereDate('created_at', $date)
                ->whereIn('status', ['success', 'failed'])
                ->count();
            $summary->transaction_count = $transactionCount;
        }
        return view('admin.settlement.list', get_defined_vars());
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
        $totalWallet = $item->ep_payout+$request->ep_payout;
        $item->usdt = $totalUsdt;
        $item->ep_payout = $totalWallet;
        $item->settled = $item->settled+$totalUsdt+$totalWallet;
        $item->save();
        $msg = "Summary Updated Successfully!";
        return redirect()->back()->with('message',$msg);
    }
}