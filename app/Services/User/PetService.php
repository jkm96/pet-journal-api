<?php

namespace App\Services\User;

use App\Http\Resources\PetProfileResource;
use App\Http\Resources\PetTraitResource;
use App\Models\Pet;
use App\Models\PetTrait;
use App\Models\User;
use App\Utils\Constants\AppConstants;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

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
            $profileUrl = $this->getPetProfileUrl($petRequest['profile_picture'], $petRequest['name']);

            $pet = Pet::create([
                'name' => $petRequest['name'],
                'nickname' => $petRequest['nickname'],
                'species' => $petRequest['species'],
                'breed' => $petRequest['breed'],
                'description' => $petRequest['description'],
                'date_of_birth' => $petRequest['date_of_birth'],
                'profile_url' => $profileUrl
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
     * @param $petSlug
     * @return JsonResponse
     */
    public function getPetProfileBySlug($petSlug): JsonResponse
    {
        try {
            $user = User::findOrFail(auth()->user()->getAuthIdentifier());
            $petProfile = $user->pets()->where('slug', $petSlug)->firstOrFail();
            $petTraits = $petProfile->petTraits;

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                new PetProfileResource($petProfile),
                'Success',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving pet profile',
                500
            );
        }
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
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
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
     * @param $updateRequest
     * @return JsonResponse
     */
    public function updatePetProfilePicture($updateRequest): JsonResponse
    {
        try {
            $user = auth()->user();
            $pet = Pet::findOrFail($updateRequest['pet_id']);

            if ($user->getAuthIdentifier() !== $pet->user_id) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to edit resource',
                    403
                );
            }

            if ($updateRequest['profile_picture']) {
                $oldProfilePicture = $pet->profile_url;
                $profileUrl = $this->getPetProfileUrl($updateRequest['profile_picture'], $pet->name);
                $pet->profile_url = $profileUrl;
                $pet->update();

                $this->deletePetProfilePicture($oldProfilePicture);

                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    $pet->profile_url,
                    'Profile picture updated successfully',
                    200
                );
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => 'The request did not contain any profile picture'],
                'No profile picture found',
                400
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet profile picture update',
                400
            );
        }
    }

    /**
     * @param $petId
     * @return JsonResponse
     */
    public function removePetProfile($petId): JsonResponse
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
                $pet->name . 's profile deleted successfully',
                200
            );

        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
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

        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
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
            $user->pets()->findOrFail($petId);
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
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during pet trait deletion',
                400
            );
        }
    }

    /**
     * @param $image
     * @param $petName
     * @return \Illuminate\Contracts\Foundation\Application|UrlGenerator|Application|string
     */
    public function getPetProfileUrl($image, $petName): \Illuminate\Contracts\Foundation\Application|UrlGenerator|string|Application
    {
        $constructName = AppConstants::$appName . '-' . $petName . '-' . Carbon::now() . '.' . $image->extension();
        $imageName = str_replace(' ', '-', $constructName);
        $image->move(public_path('images/profile_pictures'), $imageName);

        return url('images/profile_pictures/' . $imageName);
    }

    /**
     * @param $profileUrl
     * @return void
     */
    public function deletePetProfilePicture($profileUrl): void
    {
        // Extract the file path from the URL
        $filePath = public_path(parse_url($profileUrl, PHP_URL_PATH));

        // Check if the file exists before attempting to delete it
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
