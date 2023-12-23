<?php

namespace App\Services\Payments;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Utils\Enums\SubscriptionStatus;
use App\Utils\Helpers\AuthHelpers;
use App\Utils\Helpers\DatetimeHelpers;
use App\Utils\Helpers\ResponseHelpers;
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
        DB::beginTransaction();

        try {
            $startDate = Carbon::now();
            $endDate = $startDate->copy()->addMonth();

            UserSubscription::create([
                'user_id' => $user->getAuthIdentifier(),
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

            $tokenResource = AuthHelpers::getUserTokenResource($user);

            return ResponseHelpers::ConvertToJsonResponseWrapper(
                $tokenResource,
                "Payment created successfully",
                200
            );
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['error' => $e->getMessage()],
                'Error during payment creation',
                400
            );
        }
    }

}
