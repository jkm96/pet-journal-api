<?php

namespace App\Utils\Helpers;

use App\Models\CustomerSubscription;
use App\Models\SubscriptionPlan;
use App\Utils\Enums\SubscriptionStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentHelpers
{
    /**
     * @param $username
     * @return string
     */
    public static function generateUniqueInvoice($username): string
    {
        $firstLetter = substr($username, 0, 1);
        $lastLetter = substr($username, -1);
        return 'PDI' . Carbon::now()->format('dmYHi') . strtoupper($firstLetter . $lastLetter);
    }

    /**
     * @param mixed $customerId
     * @param mixed $paymentIntentId
     * @param string $uniqueInvoice
     * @return void
     */
    public static function createANewCustomerSubscription($customerId, $paymentIntentId, $uniqueInvoice): void
    {
        $subscription = SubscriptionPlan::firstOrFail();
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addMonth();

       CustomerSubscription::create([
            'customer_id' => $customerId,
            'payment_intent_id' => $paymentIntentId,
            'invoice' => $uniqueInvoice,
            'subscription_plan_id' => $subscription->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => SubscriptionStatus::ACTIVE->name,
        ]);
    }
}
