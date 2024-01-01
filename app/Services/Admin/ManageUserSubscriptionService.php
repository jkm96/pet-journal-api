<?php

namespace App\Services\Admin;

use App\Http\Resources\UserSubscriptionResource;
use App\Models\UserSubscription;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ManageUserSubscriptionService
{
    use DateFilterTrait;

    /**
     * @param $queryParams
     * @return JsonResponse
     */
    public function getUserSubscriptions($queryParams)
    {
        try {
            $query = UserSubscription::orderBy('created_at', 'desc');
            if ($queryParams['fetch_criteria'] == "all") {
               $query = $query->with(['user', 'subscriptionPlan']);
            }

            $this->applyFilters($query, $queryParams);
            $userSubscriptions = $query->get();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                UserSubscriptionResource::collection($userSubscriptions),
                'User subscriptions retrieved successfully',
                200
            );
        } catch (Exception $e) {
            Log::error('Exception when retrieving user subscriptions: ' . $e->getMessage());

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving user subscriptions',
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * @param $query
     * @param $params
     * @return void
     */
    private function applyFilters($query, $params)
    {
        $this->applyDateFilters($query, $params['period_from'] ?? null, $params['period_to'] ?? null);
        $this->applySearchTermFilter($query, $params['search_term'] ?? null);
    }

    /**
     * @param $query
     * @param $searchTerm
     * @return void
     */
    private function applySearchTermFilter($query, $searchTerm)
    {
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('username', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
    }
}
