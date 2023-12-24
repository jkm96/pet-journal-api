<?php

namespace App\Http\Controllers\User;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\UserVerificationRequest;
use App\Services\User\AuthUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class AuthUserController extends Controller
{
    /**
     * @var AuthUserService
     */
    private $_userService;

    public function __construct(AuthUserService $userService)
    {
        $this->_userService = $userService;
    }

    /**
     * Register a new user
     *
     * This endpoint lets you add a new user into the storage
     * @param UserRegistrationRequest $request
     * @return JsonResponse
     */
    public function registerUser(UserRegistrationRequest $request): JsonResponse
    {
       $origin = 'http://localhost:3000/';
        return $this->_userService->registerUser($request, $origin);
    }

    /**
     * Verify a new user email address
     *
     * This endpoint lets you verify user's email
     * @param UserVerificationRequest $request
     * @return JsonResponse
     */
    public function verifyUserEmail(UserVerificationRequest $request)
    {
        return $this->_userService->verifyUserEmail($request);
    }

    /**
     * Login user into the system
     *
     * This endpoint lets you sign in a user into the system
     * Throws an error if the login credentials are incorrect
     * @param UserLoginRequest $loginRequest
     * @return JsonResponse
     */
    public function loginUser(UserLoginRequest $loginRequest)
    {
        return $this->_userService->loginUser($loginRequest->validated());
    }

    /**
     * Logout a user
     *
     * This endpoint lets you sign out a user from the system
     * @return JsonResponse
     */
    public function logoutUser()
    {
        return $this->_userService->logoutUser();
    }
}
