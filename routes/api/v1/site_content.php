<?php

use App\Http\Controllers\Content\ContentMgmtController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/site-content', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::get('', [ContentMgmtController::class, 'getSiteContent']);
    Route::post('customer-feedback', [ContentMgmtController::class, 'saveCustomerFeedback']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('create', [ContentMgmtController::class, 'createContent']);
        Route::get('{contentId}', [ContentMgmtController::class, 'getSiteContentById']);
        Route::put('{contentId}', [ContentMgmtController::class, 'updateSiteContent']);
    });
});
