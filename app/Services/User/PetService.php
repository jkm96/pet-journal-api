<?php

namespace App\Services\User;

use App\Http\Requests\EditPetRequest;
use App\Http\Resources\PetProfileResource;
use App\Http\Resources\PetTraitResource;
use App\Http\Resources\UserResource;
use App\Models\Pet;
use App\Models\PetTrait;
use App\Models\User;
use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class PetService
{
    /**
     * @param $petRequest
     * @return JsonResponse
     * @throws \Throwable
     */
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

            if ($pet && $petRequest['pet_traits']) {
                foreach ($petRequest['pet_traits'] as $trait) {
                    $petTrait = new PetTrait();
                    $petTrait->pet_id = $pet->id;
                    $petTrait->trait = $trait['trait'];
                    $petTrait->type = $trait['type'];
                    $petTrait->saveOrFail();
                }
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new PetProfileResource($pet),
                "Pet profile created successfully",
                200
            );
        } catch (\Exception $e) {
            if ($e->getCode() === '23000') {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'The pet name must be unique for this user.'],
                    'Error:A pet with a similar name already exists in your profile',
                    400
                );
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet profile creation',
                400
            );
        }
    }

    /**
     * @return JsonResponse
     */
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

    /**
     * @param $request
     * @param $petId
     * @return JsonResponse
     */
    public function updatePetProfile($request, $petId): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = Pet::findOrFail($petId);

            if ($user->getAuthIdentifier() !== $pet->user_id) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to edit resource',
                    403
                );
            }

            $checkPet = $user->pets()->where('name', $request['name'])->first();
            if (!$checkPet) {
                $pet->name = $request['name'];
            }

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
            if ($e->getCode() === '23000') {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'The pet name must be unique for this user.'],
                    'Error: a pet with a similar name already exists',
                    400
                );
            }
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet profile update',
                400
            );
        }
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function removePet($petId): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = $user->pets()->findOrFail($petId);
            if ($user->getAuthIdentifier() !== $pet->user_id) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to edit resource',
                    403
                );
            }
            $pet->forceDelete();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $pet->name,
                $pet->name.'s profile deleted successfully',
                200
            );

        } catch (ModelNotFoundException $e) {
            return $this->itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet trait deletion',
                400
            );
        }
    }

    /**
     * @param $traitRequest
     * @param $petId
     * @param $petTraitId
     * @return JsonResponse
     */
    public function updatePetTrait($traitRequest, $petId, $petTraitId): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = $user->pets()->findOrFail($petId);
            $petTrait = PetTrait::findOrFail($petTraitId);

            // Check if the authenticated user is the owner of the associated pet
            if ($user->getAuthIdentifier() !== $petTrait->pet->user_id) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to edit resource',
                    403);
            }

            $existingTrait = $pet->petTraits()->where('trait', $traitRequest['trait'])->first();
            if ($existingTrait) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    $petTrait->trait,
                    "Error: A similar pet trait already exists under this pet profile",
                    400
                );
            }

            $petTrait->trait = $traitRequest['trait'];
            $petTrait->update();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $petTrait->id,
                "Pet trait updated successfully",
                200
            );

        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet trait update',
                400
            );
        }
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function getPetTraitsByPetId($petId): JsonResponse
    {
        $user = auth()->user();
        $pet = $user->pets()->where('id', $petId)->first();
        if (!$pet) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                [],
                'Pet not found',
                404
            );
        }

        $petTraits = $pet->petTraits()->orderBy('created_at', 'desc')->get();
        return ResponseHelpers::ConvertToJsonResponseWrapper(
            PetTraitResource::collection($petTraits),
            'Success',
            200
        );
    }

    /**
     * @param $petTraitRequest
     * @param $petId
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addPetTrait($petTraitRequest, $petId): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = $user->pets()->where('id', $petId)->first();
            if (!$pet) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Pet no found',
                    404
                );
            }

            $existingTrait = $pet->petTraits()->where('trait', $petTraitRequest['trait'])->first();
            if ($existingTrait) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    $petTraitRequest['trait'],
                    "Error: A similar pet trait already exists under this pet profile",
                    400
                );
            }

            $petTrait = new PetTrait();
            $petTrait->pet_id = $petId;
            $petTrait->trait = $petTraitRequest['trait'];
            $petTrait->type = $petTraitRequest['type'];
            $petTrait->saveOrFail();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new PetTraitResource($petTrait),
                'Pet trait created successfully',
                200
            );
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet trait creation',
                400
            );
        }
    }

    /**
     * @param $petId
     * @param $petTraitId
     * @return JsonResponse
     */
    public function removePetTrait($petId, $petTraitId): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = $user->pets()->findOrFail($petId);
            $petTrait = PetTrait::findOrFail($petTraitId);
            if ($user->getAuthIdentifier() !== $petTrait->pet->user_id) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to edit resource',
                    403);
            }
            $petTrait->forceDelete();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $petTrait->name,
                'Pet trait deleted successfully',
                200
            );

        } catch (ModelNotFoundException $e) {
            return $this->itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet trait deletion',
                400
            );
        }
    }

    /**
     * @param ModelNotFoundException|\Exception $e
     * @return JsonResponse
     */
    public function itemNotFoundError(ModelNotFoundException|\Exception $e): JsonResponse
    {
        $fullyQualifiedName = $e->getModel();
        $className = class_basename($fullyQualifiedName);

        return ResponseHelpers::ConvertToJsonResponseWrapper(
            ['error' => $className . ' not found'],
            $className . ' not found',
            404
        );
    }

}
