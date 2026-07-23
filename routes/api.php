<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PayinController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\TestEasypaisaController;
use App\Http\Controllers\Api\IbftController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::as('payin.')->prefix('payin')->group(function () {
    //Route::post('/checkout',[PayinController::class, 'checkout'])->middleware(['payment.validate', 'check.blocked.numbers', 'easypaisa.limit', 'easypaisa.pending.limit','phone.verified', 'restrict.user.transaction.range']);
    Route::post('/checkout',[PayinController::class, 'checkout'])->middleware(['block.listed.phone.carrier:payin','gateway.metrics', 'log.rejected', 'payment.validate', 'phone.verified', 'restrict.user.transaction.range', 'check.blocked.numbers', 'easypaisa.limit', 'easypaisa.pending.limit']);
});


    
    
Route::as('payout.')->prefix('payout')->group(function () {
    Route::middleware(['block.listed.phone.carrier:payout','whitelist.ip'])->group(function () {
        Route::post('/checkout',[PayoutController::class, 'getPayoutSettings']);
    });
    // Route::post('/test-jc-dist',[PayoutController::class, 'testJc']);
});

Route::as('ibft-payout.')->prefix('ibft-payout')->group(function () {
    Route::post('/checkout',[IbftController::class, 'checkout']);
});

Route::post('/payin-status-check', [GeneralController::class , 'checkStatus']);

Route::post('/nova-payout', [GeneralController::class , 'novaPayout']);
Route::get('/get-khushi-payout', [GeneralController::class , 'getKhushiPayout']);

Route::post('/login', [PayoutController::class , 'login']);

Route::get('/get-payin-data', [GeneralController::class , 'getPayinData']);
Route::post('/payout-status-check', [GeneralController::class , 'checkPayoutStatus']);
Route::get('/get-dashboard-data', [GeneralController::class , 'dashboardData']);
Route::get('/get-payout-data', [GeneralController::class , 'payoutData']);
Route::get('/get-settlement-data', [GeneralController::class , 'getSettlementData']);
Route::get('/get-prev-day-settlement-data', [GeneralController::class , 'getPrevDaySettlementData']);
Route::post('/get-coctail-data', [GeneralController::class , 'getCocktailData']);
Route::get('/add-coctail-settlements', [GeneralController::class , 'addCocktailSettlements']);
Route::post('/add-surplus-from-coctail', [GeneralController::class , 'addSurplusCocktail']);
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
Route::post('v1/test-payin-checkout', [PayinController::class, 'checkout'])
    ->middleware(['gateway.metrics', 'log.rejected']);


Route::get('v1/get-dashboard-data', [GeneralController::class , 'dashboardDataV1'])->middleware('auth.api.key');
Route::prefix('v1')->middleware(['hmac.authenticate'])->group(function () {
    //Route::post('payment-checkout', [TestPayinController::class, 'checkout']);// testing purpose only
    // payin route
    Route::post('payment-checkout', [PayinController::class, 'checkout'])
        ->middleware(['block.listed.phone.carrier:payin','gateway.metrics', 'log.rejected', 'phone.verified', 'restrict.user.transaction.range', 'easypaisa.pending.limit']);
/*
 * 
  Route::post('payment-checkout', [PayinController::class, 'checkout'])
        ->middleware('phone.verified'); 
 */
    // Payout Route
    Route::post('payout/checkout', [PayoutController::class, 'getPayoutSettings'])
        ->middleware(['block.listed.phone.carrier:payout','whitelist.ip', 'throttle:payout']);
});

Route::as('payout.')->prefix('payout')->group(function () {
        Route::post('/postman-testing/checkout',[PayoutController::class, 'getPayoutSettings']);
});

Route::any('/get-zp-callback', [GeneralController::class , 'zpCallback'])
    ->middleware('whitelist.zp.callback');

Route::as('payout.')->prefix('payout')->group(function () {
    Route::post('/zp-test/checkout',[TestEasypaisaController::class, 'payoutProceed']);
});