<?php

use App\Http\Controllers\MagicStudio\MagicStudioController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/magic-studio', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('list', [MagicStudioController::class, 'getProjects']);
        Route::get('{projectSlug}', [MagicStudioController::class, 'getProjectById']);
        Route::post('', [MagicStudioController::class, 'createMagicProject']);
        Route::post('save-pdf', [MagicStudioController::class, 'saveProjectPdf']);
        Route::delete('{projectId}/delete', [MagicStudioController::class, 'deleteProject']);
    });
});

