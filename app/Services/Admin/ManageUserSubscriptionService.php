<?php

namespace App\Services\Admin;

use App\Http\Resources\UserSubscriptionResource;
use App\Jobs\DispatchEmailNotificationsJob;
use App\Models\CustomerSubscription;
use App\Models\User;
use App\Utils\Enums\EmailTypes;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\PaymentHelpers;
use App\Utils\Helpers\ResponseHelpers;
use App\Utils\Traits\DateFilterTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageUserSubscriptionService
{
    use DateFilterTrait;

    /**
     * @param $createPaymentRequest
     * @return JsonResponse
     */
    public function adminCreateUserSubscription($createPaymentRequest)
    {
        try {
            $customerId = trim($createPaymentRequest['customer_id']);
            $customerEmail = trim($createPaymentRequest['customer_email']);
            $billingReason = trim($createPaymentRequest['billing_reason']);

            DB::beginTransaction();

            $user = User::where('customer_id', $customerId)->orWhere('email', $customerEmail)->firstOrFail();
            $uniqueInvoice = PaymentHelpers::generateUniqueInvoice($user->username);
            $existingSubscription = CustomerSubscription::where('customer_id', $customerId)
                ->where('billing_reason', $billingReason)
                ->first();

            if (!$existingSubscription) {
                PaymentHelpers::createCustomerSubscription($customerId,$billingReason, "$-paymentIntentId", $uniqueInvoice);
            }

            $existingSubscription->invoice = $uniqueInvoice;
            $existingSubscription->save();

            $user->is_subscribed = 1;
            $user->customer_id = $customerId;
            $user->save();

            DB::commit();

            $details = [
                'type' => EmailTypes::PAYMENT_CHECKOUT_CONFIRMATION->name,
                'recipientEmail' => trim($user->email),
                'username' => trim($user->username),
                'invoice' => $uniqueInvoice,
            ];

            DispatchEmailNotificationsJob::dispatch($details);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['user' => $user],
                'Payment created successfully',
                200
            );
        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error retrieving billing details',
                500
            );
        }
    }

    /**
     * @param $queryParams
     * @param $userId
     * @return JsonResponse
     */
    public function getUserSubscriptions($queryParams, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $query = $user->userSubscriptions()
                ->with(['subscriptionPlan'])
                ->orderBy('created_at', 'desc');

            //TODO apply search term filters

            $this->applyFilters($query, $queryParams);
            $userSubscriptions = $query->get();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                UserSubscriptionResource::collection($userSubscriptions),
                'User subscriptions retrieved successfully',
                200
            );

        } catch (ModelNotFoundException $e) {
            return ModelCrudHelpers::itemNotFoundError($e);
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
        } catch (Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error toggling user subscription status',
                500
            );
        }
    }
}
