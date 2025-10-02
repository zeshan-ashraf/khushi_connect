<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PayinController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\GeneralController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::as('payin.')->prefix('payin')->group(function () {
    Route::post('/checkout',[PayinController::class, 'checkout'])
        ->middleware(['payment.validate', 'check.blocked.numbers']);
});


    
    
Route::as('payout.')->prefix('payout')->group(function () {
    Route::post('/checkout',[PayoutController::class, 'checkout']);
    Route::post('/test-jc-dist',[PayoutController::class, 'testJc']);
});

Route::post('/payin-status-check', [GeneralController::class , 'checkStatus']);

Route::post('/login', [PayoutController::class , 'login']);

Route::get('/get-payin-data', [GeneralController::class , 'getPayinData']);

