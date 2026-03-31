<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PayinController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\TestEasypaisaController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::as('payin.')->prefix('payin')->group(function () {
    Route::post('/checkout',[PayinController::class, 'checkout'])->middleware(['payment.validate', 'check.blocked.numbers', 'easypaisa.limit', 'easypaisa.pending.limit']);
});


    
    
Route::as('payout.')->prefix('payout')->group(function () {
    Route::middleware('whitelist.ip')->group(function () {
        Route::post('/checkout',[PayoutController::class, 'checkout']);
    });
    // Route::post('/test-jc-dist',[PayoutController::class, 'testJc']);
});

Route::post('/payin-status-check', [GeneralController::class , 'checkStatus']);

Route::post('/login', [PayoutController::class , 'login']);

Route::get('/get-payin-data', [GeneralController::class , 'getPayinData']);
Route::post('/payout-status-check', [GeneralController::class , 'checkPayoutStatus']);
Route::get('/get-dashboard-data', [GeneralController::class , 'dashboardData']);
Route::get('/get-payout-data', [GeneralController::class , 'payoutData']);
Route::get('/get-settlement-data', [GeneralController::class , 'getSettlementData']);
Route::post('/add-wallet-transfer-amount', [GeneralController::class , 'addWalletAmount']);

// Test Routes for EasyPaisa
Route::post('/test-easypaisa', [TestEasypaisaController::class, 'testEasypaisaRequest']);
Route::get('/test-easypaisa-connectivity', [TestEasypaisaController::class, 'testConnectivity']);
Route::match(['get', 'post'], '/simple-curl-test', [TestEasypaisaController::class, 'simpleCurlTest']);

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|    
*/
Route::get('v1/get-dashboard-data', [GeneralController::class , 'dashboardDataV1'])->middleware('auth.api.key');
Route::prefix('v1')->middleware(['hmac.authenticate'])->group(function () {
    //Route::post('payment-checkout', [TestPayinController::class, 'checkout']);// testing purpose only
    // payin route
    Route::post('payment-checkout', [PayinController::class, 'checkout']);

    // Payout Route
    Route::post('payout/checkout', [PayoutController::class, 'checkout'])
        ->middleware('whitelist.ip');
});