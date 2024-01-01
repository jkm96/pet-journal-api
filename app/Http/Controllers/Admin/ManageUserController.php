<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchUsersRequest;
use App\Http\Requests\ToggleUserRequest;
use App\Services\Admin\ManageUserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ManageUserController extends Controller
{

    /**
     * @var ManageUserService
     */
    private ManageUserService $_manageUserService;

    public function __construct(ManageUserService $manageUserService)
    {
        $this->_manageUserService = $manageUserService;
    }

    /**
     * @param FetchUsersRequest $usersRequest
     * @return JsonResponse
     */
    public function getUsers(FetchUsersRequest $usersRequest)
    {
        return $this->_manageUserService->getAllUsers($usersRequest);
    }

    /**
     * @param $userId
     * @return JsonResponse
     */
    public function toggleUserStatus($userId)
    {
        return $this->_manageUserService->toggleUser($userId);
    }
}
