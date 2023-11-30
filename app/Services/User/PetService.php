<?php

namespace App\Services\User;

use App\Http\Requests\EditPetRequest;
use App\Http\Resources\PetProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Pet;
use App\Models\PetTrait;
use App\Models\User;
use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Http\JsonResponse;

class PetService
{
    public function createPetProfile($petRequest): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = Pet::create([
                'user_id' => $user->getAuthIdentifier(),
                'name' => $petRequest['name'],
                'nickname' => $petRequest['nickname'],
                'species' => $petRequest['species'],
                'breed' => $petRequest['breed'],
                'description' => $petRequest['description'],
                'date_of_birth' => $petRequest['date_of_birth'],
                'profile_url' => $petRequest['profile_url']
            ]);

            if ($pet && $petRequest['pet_traits']){
                foreach ($petRequest['pet_traits'] as $trait){
                    $petTrait = new PetTrait();
                    $petTrait->pet_id = $pet->id;
                    $petTrait->trait = $trait['trait'];
                    $petTrait->type = $trait['type'];
                    $petTrait->save();
                }
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new PetProfileResource($pet),
                "Pet profile created successfully",
                200
            );
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet profile creation',
                500
            );
        }
    }

    public function getPetProfiles(): JsonResponse
    {
        $user = auth()->user();
        $petProfiles = $user->pets()->orderBy('created_at', 'desc')->get();
        return ResponseHelpers::ConvertToJsonResponseWrapper(
            PetProfileResource::collection($petProfiles),
            "Success",
            200
        );
    }

    public function updatePetProfile($request, $petId): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = Pet::findOrFail($petId);

            if ($user->id !== $pet->user_id) {
                return ResponseHelpers::ConvertToJsonResponseWrapper([], 'Unauthorized to edit resource', 403);
            }

            $pet->name = $request['name'];
            $pet->nickname = $request['nickname'];
            $pet->species = $request['species'];
            $pet->breed = $request['breed'];
            $pet->description = $request['description'];
            $pet->date_of_birth = $request['date_of_birth'];
            $pet->update();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new PetProfileResource($pet),
                "Pet profile updated successfully",
                200
            );
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet profile update',
                500
            );
        }
    }

}
