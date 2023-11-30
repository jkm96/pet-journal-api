<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePetRequest;
use App\Http\Requests\EditPetRequest;
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
    public function createPet(CreatePetRequest $createPetRequest){
        return $this->_petService->createPetProfile($createPetRequest->validated());
    }

    /**
     * @return JsonResponse
     */
    public function getAllPetProfiles(){
        return $this->_petService->getPetProfiles();
    }

    /**
     * @param EditPetRequest $editPetRequest
     * @param $petId
     * @return JsonResponse
     */
    public function editPetProfile(EditPetRequest $editPetRequest, $petId){
        return $this->_petService->updatePetProfile($editPetRequest->validated(), $petId);
    }
}
