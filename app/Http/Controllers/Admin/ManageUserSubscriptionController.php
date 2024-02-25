<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserSubscriptionRequest;
use App\Http\Requests\FetchUserSubscriptionRequest;
use App\Services\Admin\ManageUserSubscriptionService;
use Illuminate\Http\JsonResponse;

class ManageUserSubscriptionController extends Controller
{
    private ManageUserSubscriptionService $_manageUserSubscriptionService;

    public function __construct(ManageUserSubscriptionService $manageUserSubscriptionService)
    {
        $this->_manageUserSubscriptionService = $manageUserSubscriptionService;
    }

    /**
     * @param FetchUserSubscriptionRequest $subscriptionRequest
     * @return null
     */
    public function getUserSubscriptions(FetchUserSubscriptionRequest $subscriptionRequest){
        return $this->_manageUserSubscriptionService->getUserSubscriptions($subscriptionRequest);
    }

    /**
     * @param CreateUserSubscriptionRequest $subscriptionRequest
     * @return JsonResponse
     */
    public function createUserSubscription(CreateUserSubscriptionRequest $subscriptionRequest){
        return $this->_manageUserSubscriptionService->adminCreateUserSubscription($subscriptionRequest);
    }
}
