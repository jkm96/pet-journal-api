<?php

use App\Http\Controllers\User\JournalEntryController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/journal-entry', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('', [JournalEntryController::class, 'getAllJournalEntries']);
        Route::post('create', [JournalEntryController::class, 'createJournalEntry']);
        Route::put('{journalId}/edit', [JournalEntryController::class, 'editJournalEntry']);
        Route::delete('{journalId}/delete', [JournalEntryController::class, 'deleteJournalEntry']);
        Route::get('{journalId}/attachment', [JournalEntryController::class, 'getJournalEntryAttachmentBuffers']);
        Route::post('attachment/create', [JournalEntryController::class, 'uploadJournalEntryAttachment']);
        Route::get('{slug}', [JournalEntryController::class, 'getJournalEntryBySlug']);
        Route::get('pet/{petId}', [JournalEntryController::class, 'getJournalEntriesByPet']);
    });
});
