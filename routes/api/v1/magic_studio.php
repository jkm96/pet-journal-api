<?php

use App\Http\Controllers\MagicStudio\MagicStudioController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/magic-studio', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('', [MagicStudioController::class, 'createMagicProject']);
    });
});

