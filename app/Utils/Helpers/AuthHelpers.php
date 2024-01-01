<?php

namespace App\Utils\Helpers;

use App\Http\Resources\AdminResource;
use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use Carbon\Carbon;

class AuthHelpers
{

    /**
     * @param $apiUser
     * @param $isAdmin
     * @return array
     */
    public static function getUserTokenResource($apiUser, $isAdmin): array
    {
        $token = $apiUser->createToken('auth-token-' . $apiUser->username, ['*'], Carbon::now()->addHours(12))->plainTextToken;
        $tokenDetails = $apiUser->tokens()->latest()->first();
        $tokenDetails->token = $token;

        if ($isAdmin){
            $apiUser->auth_token = $token;
            $apiUser->update();

            return [
                "token" => new TokenResource($tokenDetails),
                "user" => new AdminResource($apiUser)
            ];
        }

        $apiUser->permissions->all();
        return [
            "token" => new TokenResource($tokenDetails),
            "user" => new UserResource($apiUser)
        ];
    }
}
