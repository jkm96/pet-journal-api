<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\ManageUserSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/admin', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('register', [AdminController::class, 'registerAdmin']);
    Route::post('login', [AdminController::class, 'loginAdmin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminController::class, 'logoutAdmin']);

        Route::get('user', [ManageUserController::class, 'getUsers']);
        Route::get('user/{userId}', [ManageUserController::class, 'getUserById']);
        Route::put('user/{userId}/toggle-status', [ManageUserController::class, 'toggleUserStatus']);
        Route::put('user/{userId}/toggle-subscription', [ManageUserController::class, 'toggleUserSubscriptionStatus']);

        Route::get('user-subscriptions', [ManageUserSubscriptionController::class, 'getUserSubscriptions']);
    });
});
