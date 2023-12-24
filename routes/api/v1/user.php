<?php

use App\Http\Controllers\User\AuthUserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/user', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('register', [AuthUserController::class, 'registerUser']);
    Route::post('verify-email', [AuthUserController::class, 'verifyUserEmail']);
    Route::post('login', [AuthUserController::class, 'loginUser']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthUserController::class, 'logoutUser']);
    });
});
