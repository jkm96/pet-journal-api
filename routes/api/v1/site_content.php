<?php

use App\Http\Controllers\Content\ContentMgmtController;
use App\Http\Controllers\Payments\PaymentsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/site-content', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::get('', [ContentMgmtController::class, 'getSiteContent']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('', [ContentMgmtController::class, 'createContent']);
    });
});
