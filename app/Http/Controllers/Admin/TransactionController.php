<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\TransactionDataTable;
use App\Http\Controllers\Controller;
use App\Support\MerchantCallback;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{Transaction,ArcheiveTransaction,BackupTransaction,BlockedNumber};
use Carbon\Carbon;

class TransactionController extends Controller
{
    private $transactionDatatable;
    private $megatransactionDatatable;
    private $dragonTransactionDataTable;
    private $clientTransactionDataTable;
    protected $merchantId;
    protected $password;
    protected $transactionPostUrl;
    protected $integritySalt;
    protected $easyUsername;
    protected $easyPassword;
    protected $storeId;
    protected $accountNumber;
    protected $easyStatusUrl;
    
    public function __construct()
    {
        $this->middleware(['permission:Transactions']);
        $this->transactionDatatable = new TransactionDataTable();
        $this->merchantId = config('jazzcash.constants.MERCHANT_ID');
        $this->password = config('jazzcash.constants.PASSWORD');
        $this->jazzcashStatusUrl = config('jazzcash.constants.STATUS_INQUIRY');
        $this->integritySalt = config('jazzcash.constants.INTEGERITY_SALT');
        $this->easyUsername = config('easypaisa.prod_username');
        $this->easyPassword = config('easypaisa.prod_password');
        $this->storeId = config('easypaisa.prod_storeid');
        $this->accountNumber = config('easypaisa.account_num');
        $this->easyStatusUrl = config('easypaisa.status_inquiry_url');
    }

    public function list()
    {
        $status = null;
        $assets = ['data-table'];
        $start = request()->start_date;
        $end = request()->end_date;
        $txn_type = request()->txn_type;
        $userRole = auth()->user()->user_role;
        $client = request()->client;
        $status = request()->status;
        $baseQuery = Transaction::when($userRole !== 'Super Admin', function ($query) {
            return $query->where('user_id', auth()->id());
        })
        ->when($client, function ($query) use ($client) {
            return $query->where('user_id', $client);
        })
        ->when(request()->filled('txn_type') && $txn_type !== 'all', function ($query) use ($txn_type) {
            return $query->where('txn_type', $txn_type); // adjust if using 'payment_method'
        })
        ->when(request()->filled('status') && $status !== 'all', function ($query) use ($status) {
            return $query->where('status', $status);
        })
        ->when($start && $end, function ($query) use ($start, $end) {
            return $query->whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"]);
        }, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        });
        
        $totalPayinTransactionsCount = (clone $baseQuery)->count();
        $totalPayinSuccessCount = (clone $baseQuery)->where('status', 'success')->count();
        $totalPayinSuccessAmount = (clone $baseQuery)->where('status', 'success')->sum('amount');
        $totalPayinFailedCount = (clone $baseQuery)->where('status', 'failed')->count();
        
        $payinSuccessRate = $totalPayinTransactionsCount > 0
            ? ($totalPayinSuccessCount / $totalPayinTransactionsCount) * 100
            : 0;
        return $this->transactionDatatable->with(['user_id'=>'both','status' => $status])->render('admin.transaction.list', get_defined_vars());
    }
    public function statusInquiry($id,$type)
    {
        $integritySalt;
        $merchantId;
        $password;
        $storeId;
        $accountNumber;
        $easyUsername;
        $easyPassword;
        $item=Transaction::where('txn_ref_no',$id)->first();
	    if (!$item) {
            $item = ArcheiveTransaction::where('txn_ref_no',$id)->first();
        }
        if (!$item) {
            $item = BackupTransaction::where('txn_ref_no',$id)->first();
        }
		if($type === 'jazzcash'){
            $integritySalt = $this->integritySalt;
            $merchantId = $this->merchantId;
            $password = $this->password;
			$response=$this->jazzcashStatusFunc($id,$integritySalt,$merchantId,$password);		
	    } else{
            $storeId = $this->storeId;
            $accountNumber = $this->accountNumber;
            $easyUsername = $this->easyUsername;
            $easyPassword = $this->easyPassword;

            $response=$this->easypaisaStatusFunc($id,$storeId,$accountNumber,$easyUsername,$easyPassword);
		}
		$transactionDetails=$response;
        return view('admin.transaction.detail',get_defined_vars());
    }
	public function jazzcashStatusFunc($id,$integritySalt,$merchantId,$password)
	{
        $dataToHash = $integritySalt . '&' . $merchantId . '&' . $password . '&' . $id;
        $secureHash = hash_hmac('sha256', $dataToHash, $integritySalt);
        
        $payload = [
            'pp_MerchantID' => $merchantId,
            'pp_Password' => $password,
            'pp_TxnRefNo' => $id,
            'pp_SecureHash' => $secureHash,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->jazzcashStatusUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            // CURLOPT_SSL_VERIFYPEER => true,
            // CURLOPT_CAINFO => public_path('jazz_public_key/new-cert.crt'),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);
        return $result;
	}
    public function easypaisaStatusFunc($id,$storeId,$accountNumber,$easyUsername,$easyPassword)
    {
        $data = [
            "storeId" => $storeId,
            'orderId' => $id,
            'accountNum' => $accountNumber,
		];
        
        $credentials=base64_encode($easyUsername.':'.$easyPassword);

		$response = Http::timeout(60)->retry(3, 1000)->withHeaders([
            'credentials'=>$credentials,
            'Content-Type'=> 'application/json'
        ])->post($this->easyStatusUrl,$data);

        $result = $response->json();
        return $result;

    }
    public function easyReceipt($id)
    {
        $item=Transaction::find($id);
        return view('admin.receipt.easypaisa',get_defined_vars());
    }
    public function jazzReceipt($id)
    {
        $item=Transaction::find($id);
        return view('admin.receipt.jazzcash',get_defined_vars());
    }
    public function changeStatus(Request $request)
    {
        // Fetch the transaction first
        $transaction = Transaction::find($request->id);
    
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
    
        // Update the status
        $transaction->status = $request->status;
        $transaction->save();
        $transaction->refresh();

        MerchantCallback::notifyPayin($transaction, $transaction->user, 60, null, 'admin_change_status');
    
        return response()->json(['message' => 'Status changed successfully!']);
    }
    public function changeStatusReverse(Request $request)
    {

        // Fetch the transaction first
        $transaction = Transaction::find($request->id);
        if(!$transaction){
            $transaction=ArcheiveTransaction::find($request->id);
        }
        if(!$transaction){
            $transaction=BackupTransaction::find($request->id);
        }
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        // $settlement=Settlement::where('user_id', $transaction->user_id)
        //     ->where('date', Carbon::yesterday()->format('Y-m-d'))
        //     ->first();
        try {
            BlockedNumber::blockFromAdminReverse($transaction);

            $transaction->status = $request->status;
            $transaction->save();
        } catch (\Throwable $e) {
            Log::error('Manual reverse failed', [
                'transaction_id' => $transaction->id,
                'phone' => $transaction->phone ?? null,
                'txn_type' => $transaction->txn_type ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to reverse transaction: '.$e->getMessage(),
            ], 500);
        }

        // $settlement->closing_bal -=$transaction->amount;
        // $settlement->save();

        return response()->json(['message' => 'Status changed successfully!']);
    }
}
