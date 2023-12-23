<?php

namespace App\Utils\Helpers;

use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use Carbon\Carbon;

class AuthHelpers
{

    /**
     * @param $user
     * @return array
     */
    public static function getUserTokenResource($user): array
    {
        $token = $user->createToken('auth-token-' . $user->username, ['*'], Carbon::now()->addHours(12))->plainTextToken;
        $tokenDetails = $user->tokens()->latest()->first();
        $tokenDetails->token = $token;
        $user->permissions->all();

        return [
            "token" => new TokenResource($tokenDetails),
            "user" => new UserResource($user)
        ];
    }
}
