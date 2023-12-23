<?php

namespace App\Listeners;

use App\Models\UserSubscriptionPayment;
use App\Utils\Helpers\DatetimeHelpers;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Models\WebhookCall;
use Stripe\Event;

class PaymentCheckoutListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookCall $webhookCall)
    {
        $stripePayment = Event::constructFrom($webhookCall->payload)->data?->object;
        Log::info($stripePayment);

        $sessionCreated = DatetimeHelpers::convertUnixTimestampToCarbonInstance($stripePayment->created);
        $sessionExpires = DatetimeHelpers::convertUnixTimestampToCarbonInstance($stripePayment->expires_at);
        UserSubscriptionPayment::create([
            'session_id' => $stripePayment->id,
            'session_created' => $sessionCreated,
            'session_expires_at' =>$sessionExpires,
            'customer' => $stripePayment->customer,
            'customer_details' => json_encode($stripePayment->customer_details),
            'invoice' => $stripePayment->invoice,
            'payment_status' => $stripePayment->payment_status,
            'subscription' => $stripePayment->subscription,
        ]);

        // TODO: Send a receipt email to the customer
    }
}
