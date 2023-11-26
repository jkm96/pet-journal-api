<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;


Route::post('/admin/register', [AdminController::class, 'registerAdmin'])->name('register');
Route::post('/admin/login', [AdminController::class, 'loginAdmin'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/all', [AdminController::class, 'viewAllAdmins'])->name('all');
    Route::post('/admin/logout', [AdminController::class, 'logoutAdmin'])->name('logout');
});
