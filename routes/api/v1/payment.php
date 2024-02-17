<?php

use App\Http\Controllers\Payments\PaymentsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/payment', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('create', [PaymentsController::class, 'createPayment']);
        Route::get('billing-info/{userEmail}', [PaymentsController::class, 'getBillingInfo']);
    });
});
