<?php

namespace App\Services\Admin;

use App\Http\Resources\JournalEntryResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\Constants\AppConstants;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManageUserService
{
    public function getAllUsers($userQueryParams)
    {
        try {
            $admin = Auth::user();
            if ($admin == null) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to access resource',
                    403
                );
            }

            $query = User::orderBy('created_at', 'desc');
            $this->applyFilters($query, $userQueryParams);
            $users = $query->get();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                UserResource::collection($users),
                'Users retrieved successfully',
                200
            );
        } catch (Exception $e) {
            // Log the exception for debugging
            Log::error('Exception: ' . $e->getMessage());

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
            $admin = Auth::user();
            if ($admin == null) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    [],
                    'Unauthorized to toggle user',
                    403
                );
            }

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

    private function applyFilters($query, $params)
    {
        $this->applyDateFilters($query, $params['period_from'] ?? null, $params['period_to'] ?? null);
        $this->applySearchTermFilter($query, $params['search_term'] ?? null);
        $this->applySubscriptionFilter($query, $params['is_subscribed'] ?? null);
    }

    private function applyDateFilters($query, $periodFrom, $periodTo)
    {
        if ($periodFrom && $periodTo) {
            $dateTimeFrom = DateTime::createFromFormat('Y-m-d', $periodFrom);
            $dateTimeTo = DateTime::createFromFormat('Y-m-d', $periodTo);

            if (!$dateTimeFrom || !$dateTimeTo) {
                return ResponseHelpers::ConvertToJsonResponseWrapper([], 'Invalid date format', 400);
            }

            $query->whereBetween('created_at', [$dateTimeFrom, $dateTimeTo]);
        }
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
