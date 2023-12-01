<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePetRequest;
use App\Http\Requests\CreatePetTraitRequest;
use App\Http\Requests\EditPetRequest;
use App\Http\Requests\EditPetTraitRequest;
use App\Services\User\PetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     */
    public function createPet(CreatePetRequest $createPetRequest): JsonResponse
    {
        return $this->_petService->createPetProfile($createPetRequest->validated());
    }

    /**
     * @return JsonResponse
     */
    public function getAllPetProfiles(): JsonResponse
    {
        return $this->_petService->getPetProfiles();
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
     * @param EditPetTraitRequest $editPetTraitRequest
     * @param $petTraitId
     * @return JsonResponse
     */
    public function editPetTrait(EditPetTraitRequest $editPetTraitRequest, $petId,$petTraitId): JsonResponse
    {
        return $this->_petService->updatePetTrait($editPetTraitRequest->validated(),$petId,$petTraitId);
    }

    /**
     * @param CreatePetTraitRequest $petTraitRequest
     * @param $petId
     * @return JsonResponse
     * @throws \Throwable
     */
    public function createPetTrait(CreatePetTraitRequest $petTraitRequest, $petId): JsonResponse
    {
        return $this->_petService->addPetTrait($petTraitRequest->validated(),$petId);
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function getPetTraitsByPetId($petId): JsonResponse
    {
        return $this->_petService->getPetTraitsByPetId($petId);
    }
}
