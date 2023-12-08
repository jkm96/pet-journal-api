<?php

namespace App\Services\User;

use App\Http\Resources\JournalEntryResource;
use App\Models\JournalAttachment;
use App\Models\JournalEntry;
use App\Models\User;
use App\Utils\Constants\AppConstants;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class JournalEntryService
{

    /**
     * @param $entryRequest
     * @param $fileCount
     * @return JsonResponse
     */
    public function addJournalEntry($entryRequest,$fileCount): JsonResponse
    {
        try {
            $jsonString = $entryRequest['pet_ids'];
            $petIds = json_decode($jsonString, true);
            $validator = Validator::make(['pet_ids' => $petIds], [
                'pet_ids' => [
                    'required',
                    'array',
                    Rule::exists('pets', 'id')->whereIn('id', $petIds),
                ],
            ]);

            if ($validator->fails()) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => $validator->errors()->first('pet_ids')],
                    'Invalid pet IDs',
                    400
                );
            }

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
            ]);

            // Attach the journal entry to the specified pets
            $journalEntry->pets()->attach($petIds);

            //upload journal attachments
            $this->uploadJournalAttachments($entryRequest, $journalEntry,$fileCount);

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

    /**
     * @return JsonResponse
     */
    public function retrieveJournalEntries(): JsonResponse
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
            $journalPets = $journalEntry->pets->where('user_id', $userId)->count();

            if ($journalPets < 0) {
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
            $journalAttachments = $journalEntry->journalAttachments;
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

    /**
     * @param $entryRequest
     * @param $journalEntry
     * @param $fileCount
     * @return void
     */
    public function uploadJournalAttachments($entryRequest, $journalEntry,$fileCount): void
    {
        if ($fileCount > 0) {
            $counter = 0;
            while ($entryRequest["attachment{$counter}"]) {
                $file = $entryRequest["attachment{$counter}"];
                $journalAttachment = new JournalAttachment();
                $journalAttachment->journal_entry_id = $journalEntry->id;
                $journalAttachment->type = "image";

                $constructName = AppConstants::$appName . '-journal-entry-' . $entryRequest['event'] . '-' . Carbon::now() . '.' . $file->extension();
                $imageName = str_replace(' ', '-', $constructName);
                $file->move(public_path('images/journal_uploads'), str_replace(',', '', $imageName));
                $sourceUrl = url('images/journal_uploads/' . $imageName);
                $journalAttachment->source_url = $sourceUrl;
                $journalAttachment->save();

                $counter++;
            }
        }
    }
}
