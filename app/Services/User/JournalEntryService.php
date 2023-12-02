<?php

namespace App\Services\User;

use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Models\User;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{

    /**
     * @param $entryRequest
     * @return JsonResponse
     */
    public function addJournalEntry($entryRequest): JsonResponse
    {
        try {
            $petIds = $entryRequest['pet_ids'];
            $existingEntries = DB::table('pet_journal_entries')
                ->join('journal_entries', 'journal_entries.id', '=', 'pet_journal_entries.journal_entry_id')
                ->whereIn('pet_id', $petIds)
                ->where('title', trim($entryRequest['title']))
                ->count();

            if ($existingEntries > 0) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Journal entry with title ' . $entryRequest['title'] . ' already exists for one or more pets'],
                    'Journal entry title already exists within your collection',
                    400
                );
            }

            $journalEntry = JournalEntry::create([
                'title' => $entryRequest['title'],
                'event' => $entryRequest['event'],
                'content' => $entryRequest['content'],
                'location' => $entryRequest['location'],
                'mood' => $entryRequest['mood'],
                'tags' => $entryRequest['tags'],
                'profile_url' => $entryRequest['profile_url'],
            ]);

            // Attach the journal entry to the specified pets
            $journalEntry->pets()->attach($entryRequest['pet_ids']);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['journal_entry_id' => $journalEntry->id],
                'Journal entry created successfully',
                200
            );

        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during journal entry creation',
                400
            );
        }
    }

    public function retrieveJournalEntries()
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());

            $journalEntries = $user->pets->flatMap(function ($pet) {
                return $pet->journalEntries;
            })->unique('id');

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                JournalEntryResource::collection($journalEntries),
                'Journal entries retrieved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving journal entries',
                500
            );
        }
    }

    /**
     * @param $entryRequest
     * @param $journalEntryId
     * @return JsonResponse
     */
    public function updateJournalEntry($entryRequest, $journalEntryId): JsonResponse
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            $journalEntry = JournalEntry::findOrFail($journalEntryId);
            $petIds = $user->pets->pluck('id')->toArray();

            $existingEntries = DB::table('pet_journal_entries')
                ->join('journal_entries', 'journal_entries.id', '=', 'pet_journal_entries.journal_entry_id')
                ->whereIn('pet_id', $petIds)
                ->where('title', trim($entryRequest['title']))
                ->count();

            if ($existingEntries < 0) {
                $journalEntry->title = $entryRequest['title'];
            }

            $journalEntry->event = $entryRequest['event'];
            $journalEntry->content = $entryRequest['content'];
            $journalEntry->location = $entryRequest['location'];
            $journalEntry->mood = $entryRequest['mood'];
            $journalEntry->tags = $entryRequest['tags'];
            $journalEntry->profile_url = $entryRequest['profile_url'];
            $journalEntry->update();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['journal_entry_id' => $journalEntry->id],
                'Journal entry updated successfully',
                200
            );

        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during journal entry update',
                400
            );
        }
    }

    /**
     * @param $journalEntryId
     * @return JsonResponse
     */
    public function removeJournalEntry($journalEntryId): JsonResponse
    {
        try {
            $userId = auth()->user()->getAuthIdentifier();
            User::findOrFail($userId);
            $journalEntry = JournalEntry::findOrFail($journalEntryId);
            $journalPets = $journalEntry->pets->where('user_id',$userId)->count();

            if ($journalPets < 0){
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to edit resource',
                    403
                );
            }

            $journalEntry->pets()->detach();
            $journalEntry->delete();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $journalEntryId,
                'Journal entry deleted successfully',
                200
            );

        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error deleting journal entry',
                500
            );
        }
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function retrieveJournalEntriesByPetId($petId): JsonResponse
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            $pet = $user->pets()->findOrFail($petId);
            $journalEntries = $pet->journalEntries;
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                JournalEntryResource::collection($journalEntries),
                'Journal entries retrieved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving journal entries',
                500
            );
        }
    }

    /**
     * @param $journalEntryId
     * @return JsonResponse
     */
    public function retrieveJournalEntryById($journalEntryId): JsonResponse
    {
        try {
            User::findOrFail(auth()->user()->getAuthIdentifier());

            $journalEntry = JournalEntry::findOrFail($journalEntryId);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new JournalEntryResource($journalEntry),
                'Journal entry retrieved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving journal entries',
                500
            );
        }
    }
}
