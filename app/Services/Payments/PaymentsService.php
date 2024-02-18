<?php

namespace App\Services\Payments;

use App\Http\Resources\UserSubscriptionResource;
use App\Jobs\DispatchEmailNotificationsJob;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\UserSubscriptionPayment;
use App\Utils\Enums\EmailTypes;
use App\Utils\Enums\SubscriptionStatus;
use App\Utils\Helpers\AuthHelpers;
use App\Utils\Helpers\DatetimeHelpers;
use App\Utils\Helpers\ModelCrudHelpers;
use App\Utils\Helpers\ResponseHelpers;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentsService
{
    /**
     * @param $createPaymentRequest
     * @return JsonResponse
     */
    public function createUserPayment($createPaymentRequest)
    {
        $user = auth()->user();
        $subscription = SubscriptionPlan::firstOrFail();
        $uniqueInvoice = $this->generateUniqueInvoice($user->username);

        DB::beginTransaction();

        try {
            $startDate = Carbon::now();
            $endDate = $startDate->copy()->addMonth();

            UserSubscription::create([
                'user_id' => $user->getAuthIdentifier(),
                'invoice' => $uniqueInvoice,
                'subscription_plan_id' => $subscription->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => SubscriptionStatus::ACTIVE->name,
                'stripe_session_id' => $createPaymentRequest['session_id'],
                'stripe_subscription' => $createPaymentRequest['subscription'],
                'stripe_customer' => $createPaymentRequest['customer'],
                'stripe_created' => DatetimeHelpers::convertUnixTimestampToCarbonInstance($createPaymentRequest['created']),
                'stripe_expires_at' => DatetimeHelpers::convertUnixTimestampToCarbonInstance($createPaymentRequest['expires_at']),
                'stripe_payment_status' => $createPaymentRequest['payment_status'],
                'stripe_status' => $createPaymentRequest['status'],
            ]);

            $user->is_subscribed = 1;
            $user->save();

            DB::commit();

            $userSubscription = UserSubscriptionPayment::where('session_id', $createPaymentRequest['session_id'])
                ->where('customer', $createPaymentRequest['customer'])
                ->where('subscription', $createPaymentRequest['subscription'])
                ->first();

            if ($userSubscription) {
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
     * @param $username
     * @return string
     */
    private function generateUniqueInvoice($username)
    {
        $firstLetter = substr($username, 0, 1);
        $lastLetter = substr($username, -1);
        return 'PDI' . Carbon::now()->format('dmYHi') . strtoupper($firstLetter . $lastLetter);
    }

    public function getUserPayments($email)
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
