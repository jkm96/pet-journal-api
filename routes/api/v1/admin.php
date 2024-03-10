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

        Route::group(['prefix' => 'manage-users', 'middleware' => 'api'], function () {
            Route::get('', [ManageUserController::class, 'getUsers']);
            Route::get('{userId}', [ManageUserController::class, 'getUserById']);
            Route::put('{userId}/toggle-status', [ManageUserController::class, 'toggleUserStatus']);
        });

        Route::group(['prefix' => 'user-subscriptions', 'middleware' => 'api'], function () {
            Route::post('', [ManageUserSubscriptionController::class, 'createUserSubscription']);
            Route::get('{userId}', [ManageUserSubscriptionController::class, 'getUserSubscriptions']);
            Route::put('{userId}/toggle-subscription', [ManageUserSubscriptionController::class, 'toggleUserSubscriptionStatus']);
        });
    });
});
