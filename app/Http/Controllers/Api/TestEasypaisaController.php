<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Service\PaymentService;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\{Payout, User, Setting, SurplusAmount, PayoutSetting};

class TestEasypaisaController extends Controller
{
    public $service;
    protected $logger;

	public function __construct(PaymentService $service) 
	{
		$this->service = $service;
        $this->logger = Log::channel('zp_callback');
	}
    public function payoutProceed(Request $request)
    {
        $requestId = uniqid('req_');
        $startTime = microtime(true);
        
        $this->logger->info('Starting zp payout checkout process', [
            'request_id' => $requestId,
            'request_data' => $request->all(),
            'timestamp' => now()->toDateTimeString()
        ]);

        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'client_email' => 'required|email',
            'payout_method' => 'required|in:jazzcash,easypaisa',
            'amount' => 'required|numeric|min:1',
        ]);
    
        if ($validator->fails()) {
            $this->logger->warning('Validation failed', [
                'request_id' => $requestId,
                'errors' => $validator->errors()->toArray(),
                'execution_time' => microtime(true) - $startTime
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user= User::where('email',$request->client_email)->first();
        if(($request->payout_method == "jazzcash" && $user->payout_jc_api == 0) || ($request->payout_method == "easypaisa" && $user->payout_ep_api == 0)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error:Daily limit exceeded.',
            ], 400);
        }
        $orderId=Payout::where('orderId',$request->orderId)->first();
        if($orderId){
            $this->logger->warning('Duplicate order ID detected', [
                'request_id' => $requestId,
                'order_id' => $request->orderId,
                'execution_time' => microtime(true) - $startTime
            ]);

            $url =$request->callback_url;
            $call_data = [
                'orderId' => $request->orderId,
                'message' => 'Your payout cannot be processed due to Order Id already exist, please try again.',
                'status' => 'failed',
            ];
            $this->logger->info('Sending callback for duplicate order', [
                'request_id' => $requestId,
                'callback_url' => $url,
                'callback_data' => $call_data
            ]);
            $response = Http::timeout(60)->post($url, $call_data);
            $this->logger->info('Callback response for duplicate order', [
                'request_id' => $requestId,
                'response_status' => $response->status(),
                'response_body' => $response->json()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Your payout cannot be processed due to due to Order Id already exist, please try again.',
            ], 400);
        }
        else{
            $callback_url = $request->callback_url;
            
            $setting=Setting::where('user_id',$user->id)->first();
            $assigned_amount=0;
            if($request->payout_method == "easypaisa"){
                $assigned_amount=$setting->easypaisa;
            }else {
                $assigned_amount=$setting->jazzcash;
            }
            if($request->amount > $assigned_amount){
                $values=[
                    'user_id' => $user->id,
                    'code' => "Nova-Failed",
                    'message' => "Merchant assigned limit breached",
                    'transaction_reference' => "",
                    'amount' => $request->amount,
                    'orderId' => $request->orderId,
                    'fee' => "",
                    'phone' => $request->phone,
                    'transaction_type' => $request->payout_method,
                    'status' => 'failed',
                    'url' => $request->callback_url,
                ];
                Payout::create($values);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Merchant assigned limit breached',
                ], 400);
            }
            
            $data=$request->all();
            
            $get_zp_paarams_data=$this->getZpParams($data);
            $json_data=json_encode($get_zp_paarams_data, true);

            $transactionUrl=env('ZP_Payout_URL');

            $curl = curl_init($transactionUrl);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
            
            $response = curl_exec($curl);
            $data=json_decode($response, true);

            $values=[
                'user_id' => $user->id,
                'code' => $data['zp_RespCode'] ?? "",
                'message' => $data['zp_RespMsg'] ?? "",
                'transaction_reference' => $data['zp_TransID'] ?? "",
                'amount' => $request->amount,
                'orderId' => $request->orderId,
                'phone' => $request->phone,
                'transaction_type' => $request->payout_method,
                'transaction_id' => $data['zp_TransID'] ?? "",
                'status' => $data['zp_Status'] === 'Logged' ? 'pending' : 'failed',
                'url' => $request->callback_url,
                'api_type' => 'ZP',
            ];

            Payout::create($values);

            if($data['zp_Status'] === 'Logged' || $data['zp_Status'] === 'Paid'){
                return response()->json([
                    'success' => true,
                    'message' => 'Payout processed successfully.',
                    'transaction_id' => $data['zp_MerchantPOID'],
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your payout cannot be processed due to '. $data['zp_RespMsg']. ' , please try again.',
                ], 400);
            }
        }
    }
    public function getZpParams($data)
    {
        $now = Carbon::now();
        $txnDateTime = $now->format('YmdHis');
        $txnExpiryDateTime = $now->copy()->addMinutes(60)->format('YmdHis');
        $zp_merchant_id=env('ZP_Merchant_ID');
        $zp_callback_url=env('zp_CallBackURL');
        if($data['orderId'] == 'easypaisa'){
            $zp_walletId="003";
        }else{
            $zp_walletId="002";
        }

        $params = [
            "zp_MerchantID"        => $zp_merchant_id,
            "zp_SubMerchantID"     => "",
            "zp_WalletID"          => $zp_walletId,

            // Dynamic mapping
            "zp_MerchantPOID"      => $data['orderId'],
            "zp_MerchantPOAmount"  => $data['amount'],
            "zp_MerchantPOCell"    => '0' . substr($data['phone'], -10),
            "zp_MerchantPOCNIC"    => "null",
            "zp_MerchantPOEmail"   => "client@khushiconnect.com",
            "zp_MerchantPOName"    => "clientname",
            "zp_CallBackURL"       => $zp_callback_url,
            
            // Time fields
            "zp_TxnDateTime"       => $txnDateTime,
            "zp_TxnExpiryDateTime" => $txnExpiryDateTime,

            // Will generate later
            "zp_SecureHash"        => ""
        ];

        // Generate Secure Hash
        $params['zp_SecureHash'] = $this->generateHash($params);
        // dd($params);
        return $params;
    }
    private function generateHash($params)
    {
        $zp_payout_password=env('ZP_Payout_Password');
        $zp_payout_key=env('ZP_Payout_Key');

        $extra_params=[
            // Extra Fields
            "zp_Disb_TxnType " => "1",
            "zp_Disb_API_Version " => "1.5",
            "zp_Currency" => "PKR",
            "zp_MerchantPWD" => $zp_payout_password,
            "zp_MerchantKey" => $zp_payout_key,
        ];

        $hashParams = array_merge($params, $extra_params);

        $filtered = [];
        foreach ($hashParams as $key => $value) {
            if (str_starts_with($key, 'zp_') && $value !== "" && $value !== null) {
                $filtered[$key] = $value;
            }
        }

        ksort($filtered);

        $hashString = $zp_payout_key;
        foreach ($filtered as $value) {
            $hashString .= "&" . $value;
        }

        return hash_hmac('sha256', $hashString, $zp_payout_key);
    }
}
