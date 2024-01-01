<?php

namespace App\Services\Admin;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ManageUserService
{
    use DateFilterTrait;
    public function getAllUsers($userQueryParams)
    {
        try {
            $query = User::orderBy('created_at', 'desc');
            $this->applyFilters($query, $userQueryParams);
            $users = $query->get();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                UserResource::collection($users),
                'Users retrieved successfully',
                200
            );
        } catch (Exception $e) {
            Log::error('Exception when retrieving users: ' . $e->getMessage());

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving users',
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * @param $userId
     * @return JsonResponse
     */
    public function toggleUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->is_active = $user->is_active ? 0 : 1;
            $user->update();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['user' => $user],
                'User toggled successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error toggling user',
                500
            );
        }
    }

    /**
     * @param $userId
     * @return JsonResponse
     */
    public function toggleUserSubscription($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->is_subscribed = $user->is_subscribed ? 0 : 1;
            $user->update();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['user' => $user],
                'User subscription status toggled successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error toggling user subscription status',
                500
            );
        }
    }

    private function applyFilters($query, $params)
    {
        $this->applyDateFilters($query, $params['period_from'] ?? null, $params['period_to'] ?? null);
        $this->applySearchTermFilter($query, $params['search_term'] ?? null);
        $this->applySubscriptionFilter($query, $params['is_subscribed'] ?? null);
    }

    private function applySearchTermFilter($query, $searchTerm)
    {
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('username', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
    }

    private function applySubscriptionFilter($query, $subscribed)
    {
        if ($subscribed !== null) {
            $query->where('is_subscribed', $subscribed);
        }
    }
}
