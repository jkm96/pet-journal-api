<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/admin', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('register', [AdminController::class, 'registerAdmin']);
    Route::post('login', [AdminController::class, 'loginAdmin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('all', [AdminController::class, 'viewAllAdmins']);
        Route::post('logout', [AdminController::class, 'logoutAdmin']);
    });
});
