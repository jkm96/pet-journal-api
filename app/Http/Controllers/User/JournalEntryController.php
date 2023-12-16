<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Services\User\JournalEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JournalEntryController extends Controller
{
    private JournalEntryService $_journalEntryService;

    /**
     * @param JournalEntryService $journalEntryService
     */
    public function __construct(JournalEntryService $journalEntryService)
    {
        $this->_journalEntryService = $journalEntryService;
    }

    /**
     * @param CreateJournalEntryRequest $entryRequest
     * @return null
     */
    public function createJournalEntry(CreateJournalEntryRequest $entryRequest){
        $fileCount = $entryRequest->files->count();
        return $this->_journalEntryService->addJournalEntry($entryRequest,$fileCount);
    }

    /**
     * @param UpdateJournalEntryRequest $entryRequest
     * @param $journalId
     * @return JsonResponse
     */
    public function editJournalEntry(UpdateJournalEntryRequest $entryRequest, $journalId){
        return $this->_journalEntryService->updateJournalEntry($entryRequest, $journalId);
    }

    /**
     * @param $journalId
     * @return JsonResponse
     */
    public function deleteJournalEntry($journalId){
        return $this->_journalEntryService->removeJournalEntry($journalId);
    }

    /**
     * @return JsonResponse
     */
    public function getAllJournalEntries(): JsonResponse
    {
        return $this->_journalEntryService->retrieveJournalEntries();
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function getJournalEntriesByPet($petId): JsonResponse
    {
        return $this->_journalEntryService->retrieveJournalEntriesByPetId($petId);
    }

    /**
     * @param $slug
     * @return JsonResponse
     */
    public function getJournalEntryBySlug($slug){
        return $this->_journalEntryService->retrieveJournalEntryBySlug($slug);
    }

    /**
     * @param $journalId
     * @return JsonResponse
     */
    public function getJournalEntryAttachmentBuffers($journalId){
        return $this->_journalEntryService->retrieveJournalEntryAttachmentBuffers($journalId);
    }

    /**
     * @param Request $uploadRequest
     * @return null
     */
    public function uploadJournalEntryAttachment(Request $uploadRequest){
        $fileCount = $uploadRequest->files->count();
        return $this->_journalEntryService->addJournalEntryAttachments($uploadRequest,$fileCount);
    }
}
