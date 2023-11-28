<?php

namespace App\Services\User;

use App\Exceptions\CustomException;
use App\Http\Requests\UserVerificationRequest;
use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserVerification;
use App\Utils\Enums\PetJournalPermission;
use App\Utils\Enums\SubscriptionStatus;
use App\Utils\Helpers\ResponseHelpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthUserService
{
    /**
     * @param $request
     * @return JsonResponse
     */
    public function registerUser($request): JsonResponse
    {
        try {
            $user = User::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]);

            foreach (PetJournalPermission::cases() as $journalPermission) {
                $userPermission = new Permission();
                $userPermission->user_id = $user->id;
                $userPermission->name = $journalPermission->name;
                $userPermission->value = $journalPermission->value;
                $userPermission->save();
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper(new UserResource($user),"Registered successfully'", 200);

        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(['error' => $e->getMessage()], 'Error during registration', 500);
        }
    }


    /**
     * @param $verificationRequest
     * @return JsonResponse
     */
    public function verifyUserEmail($verificationRequest): JsonResponse
    {
        $userVerify = UserVerification::where('token', $verificationRequest->token)->first();
        $message = 'Email cannot be recognized';

        if (!is_null($userVerify)){
            $user = $userVerify->user;

            if (!$user->is_email_verified){
                $userVerify->user->is_email_verified = 1;
                $userVerify->user->email_verified_at = Carbon::now();
                $userVerify->user->save();
                $message = "Email has been verified. You can now login";
            }else{
                $message = "Seems like your email is already verified. Kindly login";
            }
        }

        return ResponseHelpers::ConvertToJsonResponseWrapper([],$message, 200);
    }

    /**
     * @return JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::all();
        return ResponseHelpers::ConvertToJsonResponseWrapper(UserResource::collection($users),"success", 200);
    }

    /**
     * @param $loginRequest
     * @return JsonResponse
     */
    public function loginUser($loginRequest): JsonResponse
    {
        $credentials = filter_var($loginRequest['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if(Auth::attempt(array($credentials=>$loginRequest['username'], 'password'=>$loginRequest['password']))){
            $user = User::where('email', $loginRequest['username'])
                ->orWhere('username',$loginRequest['username'])
                ->firstOrFail();

            $token = $user->createToken('authToken')->plainTextToken;
            $user->permissions->all();
            $tokenResource = [
                "token" => $token,
                "user"=> new UserResource($user)
            ];
            return ResponseHelpers::ConvertToJsonResponseWrapper($tokenResource,"logged in successfully", 200);
        }

        return ResponseHelpers::ConvertToJsonResponseWrapper([],"Login information is invalid.", 401);
    }

    /**
     * @return JsonResponse
     */
    public function logoutUser(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return ResponseHelpers::ConvertToJsonResponseWrapper([],"logged out successfully", 200);
    }
}
