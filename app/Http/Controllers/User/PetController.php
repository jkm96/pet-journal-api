<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePetRequest;
use App\Http\Requests\CreatePetTraitRequest;
use App\Http\Requests\EditPetProfilePictureRequest;
use App\Http\Requests\EditPetRequest;
use App\Http\Requests\EditPetTraitRequest;
use App\Services\User\PetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PetController extends Controller
{
    /**
     * @var PetService
     */
    private PetService $_petService;

    public function __construct(PetService $petService)
    {
        $this->_petService = $petService;
    }

    /**
     * @param CreatePetRequest $createPetRequest
     * @return JsonResponse
     * @throws \Throwable
     */
    public function createPet(CreatePetRequest $createPetRequest): JsonResponse
    {
        Log::info($createPetRequest);
        return $this->_petService->createPetProfile($createPetRequest);
    }

    /**
     * @return JsonResponse
     */
    public function getAllPetProfiles(): JsonResponse
    {
        return $this->_petService->getPetProfiles();
    }

    /**
     * @param $slug
     * @return JsonResponse
     */
    public function getPetProfileBySlug($slug): JsonResponse
    {
        return $this->_petService->getPetProfileBySlug($slug);
    }

    /**
     * @param EditPetRequest $editPetRequest
     * @param $petId
     * @return JsonResponse
     */
    public function editPetProfile(EditPetRequest $editPetRequest, $petId): JsonResponse
    {
        return $this->_petService->updatePetProfile($editPetRequest->validated(), $petId);
    }

    /**
     * @param EditPetProfilePictureRequest $editRequest
     * @return JsonResponse
     */
    public function editPetProfilePicture(EditPetProfilePictureRequest $editRequest): JsonResponse
    {
        return $this->_petService->updatePetProfilePicture($editRequest);
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function deletePetProfile($petId): JsonResponse
    {
        return $this->_petService->removePetProfile($petId);
    }

    /**
     * @param EditPetTraitRequest $editPetTraitRequest
     * @param $petId
     * @param $petTraitId
     * @return JsonResponse
     */
    public function editPetTrait(EditPetTraitRequest $editPetTraitRequest, $petId,$petTraitId): JsonResponse
    {
        return $this->_petService->updatePetTrait($editPetTraitRequest,$petId,$petTraitId);
    }

    /**
     * @param CreatePetTraitRequest $petTraitRequest
     * @param $petId
     * @return JsonResponse
     * @throws \Throwable
     */
    public function createPetTrait(CreatePetTraitRequest $petTraitRequest, $petId): JsonResponse
    {
        return $this->_petService->addPetTrait($petTraitRequest,$petId);
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function getPetTraitsByPetId($petId): JsonResponse
    {
        return $this->_petService->getPetTraitsByPetId($petId);
    }

    /**
     * @param $petId
     * @param $petTraitId
     * @return JsonResponse
     */
    public function deletePetTrait($petId, $petTraitId): JsonResponse
    {
        return $this->_petService->removePetTrait($petId,$petTraitId);
    }
}
