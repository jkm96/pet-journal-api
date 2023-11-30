<?php

use App\Http\Controllers\User\AuthUserController;
use App\Http\Controllers\User\PetController;
use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/user', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('register', [AuthUserController::class, 'registerUser']);
    Route::post('login', [AuthUserController::class, 'loginUser']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('all', [AuthUserController::class, 'viewAllUsers']);
        Route::post('logout', [AuthUserController::class, 'logoutUser']);
    });
});

Route::group(['prefix' => 'v1/pet', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profiles', [PetController::class, 'getAllPetProfiles']);
        Route::post('create', [PetController::class, 'createPet']);
        Route::put('edit/{petId}', [PetController::class, 'editPetProfile']);
    });
});
