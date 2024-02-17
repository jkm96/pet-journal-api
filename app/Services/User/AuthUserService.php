<?php

namespace App\Services\User;

use App\Jobs\DispatchEmailNotificationsJob;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserVerification;
use App\Utils\Constants\AppConstants;
use App\Utils\Enums\EmailTypes;
use App\Utils\Enums\PetJournalPermission;
use App\Utils\Helpers\AuthHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthUserService
{
    /**
     * @param $request
     * @return JsonResponse
     */
    public function registerUser($request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $userAvatarUrl = $this->createUserAvatarFromName(trim($request['username']), true);
            $profileCoverUrl = $this->createUserAvatarFromName(trim($request['username']), false);

            $user = User::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'profile_url' => $userAvatarUrl,
                'profile_cover_url' => $profileCoverUrl,
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
            $verificationUrl = env('PET_DIARIES_FRONTEND_URL'). '/auth/verify-user?token=' . $token;
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

            DispatchEmailNotificationsJob::dispatch($details);

            $tokenResource = AuthHelpers::getUserTokenResource($user, 0);

            return ResponseHelpers::ConvertToJsonResponseWrapper($tokenResource, "Registered successfully'", 200);
        } catch (Exception $e) {
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
        $message = 'Verification token has expired.';

        if (!is_null($userVerify)) {
            $user = $userVerify->user;
            Log::info(json_encode($user));

            $expirationDate = Carbon::parse($userVerify->created_at)->addDays(7);
            if (Carbon::now()->gt($expirationDate)) {
                $userVerify->delete();
                return ResponseHelpers::ConvertToJsonResponseWrapper([], $message, 400);
            }

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
        } catch (Exception $e) {
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

    /**
     * @param $username
     * @param $isProfilePicture
     * @return string
     */
    private function createUserAvatarFromName($username, $isProfilePicture): string
    {
        // Extract first and last letters as initials
        $name = trim($username);
        $initials = strtoupper(substr($name, 0, 1));
        $lastLetterIndex = strlen($name) - 1;
        if ($lastLetterIndex > 0) {
            $initials .= strtoupper(substr($name, $lastLetterIndex, 1)); // Last letter
        }

        // Define a background color and text color for the avatar
        $bgColor = '#'.substr(md5($name), 0, 6); // Use a unique color based on the name
        $textColor = '#ffffff';

        // Create an image with the initials and colors
        $image = $isProfilePicture ? imagecreatetruecolor(200, 200) : imagecreatetruecolor(970, 260);
        $bg = imagecolorallocate($image, hexdec(substr($bgColor, 1, 2)), hexdec(substr($bgColor, 3, 2)), hexdec(substr($bgColor, 5, 2)));
        $text = imagecolorallocate($image, hexdec(substr($textColor, 1, 2)), hexdec(substr($textColor, 3, 2)), hexdec(substr($textColor, 5, 2)));
        imagefill($image, 0, 0, $bg);
        $font = public_path('fonts/robotoregular.ttf');
        imagettftext($image, 75, 0, 25, 130, $text, $font, $initials);

        // Save the image to a file
        $constructName = AppConstants::$appName . '-' . $username . '-' . Carbon::now() . '.png';
        $imageName = Str::lower(str_replace(' ', '-', $constructName));
        $directoryPath = $isProfilePicture ? 'images/user_profile_covers/' : 'images/user_profile_avatars/';
        if (!file_exists(public_path($directoryPath))) {
            mkdir($directoryPath, 0777, true);
        }
        imagepng($image, public_path($directoryPath . $imageName));
        imagedestroy($image);

        return url($directoryPath . $imageName);
    }
}
