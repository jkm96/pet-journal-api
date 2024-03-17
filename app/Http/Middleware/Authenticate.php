<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * @throws CustomException
     */
    protected function redirectTo(Request $request): ?string
    {
        throw new CustomException([], "You are not authenticated", 401);
    }
}
