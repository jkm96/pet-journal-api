<?php

namespace App\Services\User;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserVerification;
use App\Utils\Enums\EmailTypes;
use App\Utils\Enums\PetJournalPermission;
use App\Utils\Helpers\AuthHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthUserService
{
    /**
     * @param $request
     * @param $origin
     * @return JsonResponse
     */
    public function registerUser($request, $origin): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'is_active' => 1
            ]);

            foreach (PetJournalPermission::cases() as $journalPermission) {
                $userPermission = new Permission();
                $userPermission->user_id = $user->id;
                $userPermission->name = $journalPermission->name;
                $userPermission->value = $journalPermission->value;
                $userPermission->save();
            }

            //send email verification message
            $token = Str::random(70);
            $verificationUrl = $origin . 'auth/verify-user?token=' . $token;

            UserVerification::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            DB::commit();

            $details = [
                'type' => EmailTypes::USER_VERIFICATION->name,
                'recipientEmail' => trim($request['email']),
                'username' => trim($request['username']),
                'verificationUrl' => trim($verificationUrl),
            ];

//            DispatchEmailNotificationsJob::dispatch($details);

            $tokenResource = AuthHelpers::getUserTokenResource($user);
            return ResponseHelpers::ConvertToJsonResponseWrapper($tokenResource, "Registered successfully'", 200);
        } catch (\Exception $e) {
            DB::rollBack();
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

        if (!is_null($userVerify)) {
            $user = $userVerify->user;

            if (!$user->is_email_verified) {
                $userVerify->user->is_email_verified = 1;
                $userVerify->user->email_verified_at = Carbon::now();
                $userVerify->user->save();
                $message = "Email has been verified.";
            } else {
                $message = "Seems like your email is already verified.";
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper([], $message, 200);
        }

        return ResponseHelpers::ConvertToJsonResponseWrapper([], $message, 400);
    }

    /**
     * @param $loginRequest
     * @return JsonResponse
     */
    public function loginUser($loginRequest): JsonResponse
    {
        try {
            $credentials = filter_var($loginRequest['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            if (Auth::attempt([$credentials => $loginRequest['username'], 'password' => $loginRequest['password'], 'is_active' => 1])) {
                $user = User::where('email', $loginRequest['username'])
                    ->orWhere('username', $loginRequest['username'])
                    ->firstOrFail();

                $tokenResource = AuthHelpers::getUserTokenResource($user,0);

                return ResponseHelpers::ConvertToJsonResponseWrapper($tokenResource, "logged in successfully", 200);
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper([], "Login information is invalid.", 401);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(['error' => $e->getMessage()], 'Error during user login', 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function logoutUser(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return ResponseHelpers::ConvertToJsonResponseWrapper([], "logged out successfully", 200);
    }
}
