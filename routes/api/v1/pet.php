<?php

use App\Http\Controllers\User\PetController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/pet', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profiles', [PetController::class, 'getAllPetProfiles']);
        Route::post('create', [PetController::class, 'createPet']);
        Route::put('{petId}/edit', [PetController::class, 'editPetProfile']);
        Route::get('{slug}/profile', [PetController::class, 'getPetProfileBySlug']);
        Route::post('edit-profile-picture', [PetController::class, 'editPetProfilePicture']);
        Route::delete('{petId}/delete', [PetController::class, 'deletePetProfile']);
    });
});

Route::group(['prefix' => 'v1/pet-trait', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('{petId}', [PetController::class, 'getPetTraitsByPetId']);
        Route::post('{petId}/create', [PetController::class, 'createPetTrait']);
        Route::put('{petId}/edit/{petTraitId}', [PetController::class, 'editPetTrait']);
        Route::delete('{petId}/delete/{petTraitId}', [PetController::class, 'deletePetTrait']);
    });
});
