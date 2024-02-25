<?php

namespace App\Services\Payments;

use App\Http\Resources\UserSubscriptionResource;
use App\Jobs\DispatchEmailNotificationsJob;
use App\Models\CustomerPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\CustomerSubscription;
use App\Models\CustomerPaymentEvent;
use App\Utils\Enums\EmailTypes;
use App\Utils\Enums\SubscriptionStatus;
use App\Utils\Helpers\AuthHelpers;
use App\Utils\Helpers\DatetimeHelpers;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\PaymentHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentsService
{
    /**
     * @param $createPaymentRequest
     * @return JsonResponse
     */
    public function createUserPayment($createPaymentRequest)
    {
        $user = auth()->user();
        $uniqueInvoice = PaymentHelpers::generateUniqueInvoice($user->username);
        $customerId = $createPaymentRequest['customer'];
        $paymentIntentId = $createPaymentRequest['payment_intent'];

        try {
            DB::beginTransaction();

            $existingSubscription = CustomerSubscription::where('customer_id', $customerId)
                ->first();

            if (!$existingSubscription) {
                PaymentHelpers::createANewCustomerSubscription($customerId, $paymentIntentId, $uniqueInvoice);
            }

            $existingSubscription->invoice = $uniqueInvoice;
            $existingSubscription->save();

            $user->is_subscribed = 1;
            $user->customer_id = $customerId;
            $user->save();

            DB::commit();

            $customerPayment = CustomerPayment::where('customer_id', $customerId)
                ->first();

            if ($customerPayment) {
                $details = [
                    'type' => EmailTypes::PAYMENT_CHECKOUT_CONFIRMATION->name,
                    'recipientEmail' => trim($user->email),
                    'username' => trim($user->username),
                    'invoice' => $uniqueInvoice,
                ];

                DispatchEmailNotificationsJob::dispatch($details);
            }

            $tokenResource = AuthHelpers::getUserTokenResource($user, 0);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $tokenResource,
                "Payment created successfully",
                200
            );
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during payment creation',
                400
            );
        }
    }

    /**
     * @param $email
     * @return JsonResponse
     */
    public function getUserPayments($email): JsonResponse
    {
        try {
            $user = User::where('email', trim($email))->firstOrFail();
            if ($user->id != auth()->user()->getAuthIdentifier()) {
                return ResponseHelpers::ConvertToJsonResponseWrapper(
                    ['error' => 'Restricted content'],
                    'Error retrieving billing details',
                    401
                );
            }

            $userSubscriptions = $user->userSubscriptions()
                ->with(['subscriptionPlan'])
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                UserSubscriptionResource::collection($userSubscriptions),
                'Billing info retrieved successfully',
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
}
