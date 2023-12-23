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
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class JournalEntryService
{

    /**
     * @param $entryRequest
     * @param $fileCount
     * @return JsonResponse
     */
    public function addJournalEntry($entryRequest, $fileCount): JsonResponse
    {
        try {
            User::findOrFail(auth()->user()->getAuthIdentifier());
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
            $this->uploadJournalAttachments($entryRequest, $journalEntry, $fileCount);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['journal_entry_id' => $journalEntry->id],
                'Journal entry created successfully',
                200
            );

        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during journal entry creation',
                400
            );
        }
    }

    /**
     * @param $queryParams
     * @return JsonResponse
     */
    public function retrieveJournalEntries($queryParams): JsonResponse
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            if ($queryParams['fetch'] == "all"){
                $query = $user->journalEntries()
                    ->with(['pets', 'journalAttachments'])
                    ->orderBy('created_at', 'desc');
            }else{
                $query = $user->journalEntries()
                    ->orderBy('created_at', 'desc');
            }

            $searchTerm = $queryParams['search_term'];
            if ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%');
            }

            $periodFrom = $queryParams['period_from'];
            $periodTo = $queryParams['period_to'];
            if ($periodFrom && $periodTo) {
                $dateTimeFrom = DateTime::createFromFormat('Y-m-d', $periodFrom);
                $dateTimeTo = DateTime::createFromFormat('Y-m-d', $periodTo);
                $query->whereBetween('created_at', [$dateTimeFrom, $dateTimeTo]);
            }

            $journalEntries = $query->get();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                JournalEntryResource::collection($journalEntries),
                'Journal entries retrieved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
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

            if ($existingEntries == 0) {
                $journalEntry->title = $entryRequest['title'];
            }

            $newPetIds = $entryRequest['pet_ids'];
            $validPetIds = $user->pets->pluck('id')->intersect($newPetIds)->toArray();
            $journalEntry->pets()->sync($validPetIds);

            $journalEntry->event = $entryRequest['event'];
            $journalEntry->content = $entryRequest['content'];
            $journalEntry->location = $entryRequest['location'];
            $journalEntry->mood = $entryRequest['mood'];
            $journalEntry->tags = $entryRequest['tags'];
            $journalEntry->update();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['journal_entry_id' => $journalEntry->id],
                'Journal entry updated successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
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
            // delete associated attachments and remove media from storage
            foreach ($journalEntry->journalAttachments as $attachment) {
                ModelCrudHelpers::deleteImageFromStorage($attachment->source_url);
                $attachment->delete();
            }

            $journalEntry->delete();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $journalEntryId,
                'Journal entry deleted successfully',
                200
            );

        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving journal entries',
                500
            );
        }
    }

    /**
     * @param $journalSlug
     * @return JsonResponse
     */
    public function retrieveJournalEntryBySlug($journalSlug): JsonResponse
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            $journalEntry = $user->journalEntries()->where('slug', $journalSlug)->firstOrFail();
            $journalAttachments = $journalEntry->journalAttachments;
            $pets = $journalEntry->pets;

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new JournalEntryResource($journalEntry),
                'Journal entry retrieved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving journal entries',
                500
            );
        }
    }

    /**
     * @param $journalId
     * @return JsonResponse
     */
    public function retrieveJournalEntryAttachmentBuffers($journalId): JsonResponse
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            $journalEntry = $user->journalEntries()->where('id', $journalId)->firstOrFail();
            $journalAttachments = $journalEntry->journalAttachments;
            $buffers = [];

            foreach ($journalAttachments as $attachment) {
                $sourceUrl = $attachment->source_url;
                list($extension, $imageContents) = ModelCrudHelpers::getImageBuffer($sourceUrl);
                $buffers[] = [
                    'image_buffer' => $imageContents,
                    'image_type' => $extension,
                ];
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $buffers,
                'Journal attachment buffers retrieved successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
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
    public function uploadJournalAttachments($entryRequest, $journalEntry, $fileCount): void
    {
        if ($fileCount > 0) {
            $counter = 0;
            while ($entryRequest["attachment{$counter}"]) {
                $file = $entryRequest["attachment{$counter}"];
                $journalAttachment = new JournalAttachment();
                $journalAttachment->journal_entry_id = $journalEntry->id;
                $journalAttachment->type = "image";

                $constructName = AppConstants::$appName.'-'.$counter. '-journal-entry-' . $journalEntry->title . '-' . Carbon::now() . '.' . $file->extension();
                $imageName = Str::lower(str_replace(' ', '-', $constructName));
                $file->move(public_path('images/journal_uploads'), $imageName);
                $sourceUrl = url('images/journal_uploads/' . $imageName);
                $journalAttachment->source_url = $sourceUrl;
                $journalAttachment->save();
                $counter++;
            }
        }
    }

    /**
     * @param $uploadRequest
     * @param int $fileCount
     * @return JsonResponse
     */
    public function addJournalEntryAttachments($uploadRequest, int $fileCount): JsonResponse
    {
        try {
            Log::info($uploadRequest);
            $journalId = (int)$uploadRequest['journal_id'];
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            $journalEntry = $user->journalEntries()->where('id', $journalId)->firstOrFail();
            $this->uploadJournalAttachments($uploadRequest, $journalEntry, $fileCount);
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['message' => $fileCount . 'files uploaded successfully'],
                $fileCount.' files uploaded successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error uploading attachments',
                500
            );
        }
    }
}
