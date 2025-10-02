<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Service\PaymentService;
use DB;
use Illuminate\Support\Facades\Log;
use Zfhassaan\Easypaisa\Easypaisa;

class ProductController extends Controller
{
    public $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }
    public function index($slug)
    {
        return view('products.' . $slug);
    }
    public function show()
    {
        $list = Product::get();
        return view('frontend.products', get_defined_vars());
    }
    public function save($id)
    {
        $item = Product::find($id);
        return view('frontend.cart', get_defined_vars());
    }
    public function checkoutProceed(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'email' => 'required',
            'client_email' => 'required',
            'amount' => 'required|numeric',
        ]);
        // dd($request->all());
        // try {
            list($post_data, $type, $url) = $this->service->process($request);
            // dd($post_data, $type, $url);
            if ($type == "easypaisa") {
                try {
                    $easypaisa = new Easypaisa;
                    $response = $easypaisa->sendRequest($post_data);
                    $responseCode = $response['responseCode'];
                    $responseDesc = $response['responseDesc'];
                    if ($responseCode != '0000') {
                        return redirect()->route('index')->with('error', 'Your transaction cannot be processed, please try again.');
                    }
                    if ($responseCode == '0000') {
                        return view('front.cart.success', get_defined_vars());
                    }
                } catch (\Exception $e) {
                    return redirect()->route('index')->with('error', 'Your transaction cannot be processed, please try again.');
                }
            } else {
                $encode_data = json_encode($post_data, false);
                // dd($encode_data);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $encode_data,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                // dd($response);
                curl_close($curl);

                if ($response === false) {
                    throw new \Exception('CURL Error: ' . curl_error($curl));
                }

                $result = json_decode($response, false);
                if (isset($result->pp_ResponseCode) && $result->pp_ResponseCode == '000') {
                    $values = [
                        'phone' => $request->phone,
                        'txn_ref_no' => $result->pp_TxnRefNo,
                        'amount' => $request->amount,
                        'orderId' => $result->pp_TxnRefNo,
                        'status' => 'success',
                        'txn_type' => 'jazzcash',
                        'pp_code' => $result->pp_ResponseCode,
                        'pp_message' => $result->pp_ResponseMessage,
                        'transactionId' => $request->phone,
                    ];
                    $transaction = Transaction::create($values);
                
                    return view('frontend.success', get_defined_vars())->with('success', 'Trannsaction succesfully completed! Thanks for Choosing Jazzcash.');
                    // return redirect()->route('index')->with('success', 'Trannsaction succesfully completed! Thanks for Choosing Jazzcash.');
                }
                return redirect()->route('home')->with('error', 'Your transaction cannot be processed, please try again.');
            }
        // } catch (\Exception $e) {
        //     // dd($e->getMessage());
        //     Log::error('Checkout Proceed Error: ' . $e->getMessage());
        //     return redirect()->route('home')->with('error', 'An error occurred during the transaction. Please try again.');
        // }
    }


    public function checkoutConfirm(Request $request)
    {
        if ($request->has('auth_token')) {
            $postBackURL = $request->postBackURL;
            return view('front.cart.easypaisa-index', get_defined_vars());
        }
        if ($request->has('message')) {
            if ($request->message == null) {
                return view('front.cart.success', get_defined_vars());
            }
        }
        return redirect()->route('index')->with('error', 'Your Transaction cannot be process please try again.');
    }
    public function checkoutSuccess(Request $request)
    {
        //if the transaction is successful, it will return an array with index "desc".
        if ($request->has('desc') && $request->desc == "0000") {
            // redirect to final close page with amount

            return view('front.cart.success', get_defined_vars());
        } else {
            // redirect to error page then close.
            return redirect()->route('index')->with('error', 'Your Transaction cannot be process please try again.');
        }
    }
    
}
