<?php

use App\Http\Controllers\User\AuthUserController;
use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/user', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('register', [AuthUserController::class, 'registerUser'])->name('register');
    Route::post('login', [AuthUserController::class, 'loginUser'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('all', [AuthUserController::class, 'viewAllUsers'])->name('all');
        Route::post('logout', [AuthUserController::class, 'logoutUser'])->name('logout');
    });
});
