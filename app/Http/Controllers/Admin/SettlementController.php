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

        // Get all dates for matching
        $dates = $results->pluck('date')->toArray();

        // Fetch counts in ONE QUERY
        $transactionCounts = Transaction::where('user_id', $id)
            ->whereIn('status', ['success', 'failed'])
            ->whereIn(DB::raw('DATE(created_at)'), $dates)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        // Attach count to each settlement
        foreach ($results as $summary) {
            $summary->transaction_count = $transactionCounts[$summary->date] ?? 0;
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
        $totalWallet = $item->ep_payout+$request->ep_payout;
        $item->usdt = $totalUsdt;
        $item->ep_payout = $totalWallet;
        $item->settled = $item->settled+$totalUsdt+$totalWallet;
        $item->save();
        $msg = "Summary Updated Successfully!";
        return redirect()->back()->with('message',$msg);
    }
}