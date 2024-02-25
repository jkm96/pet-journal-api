<?php

namespace App\Listeners;

use App\Http\Resources\TokenResource;
use App\Jobs\DispatchEmailNotificationsJob;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentEvent;
use App\Models\CustomerSubscription;
use App\Models\User;
use App\Utils\Enums\EmailTypes;
use App\Utils\Helpers\DatetimeHelpers;
use App\Utils\Helpers\PaymentHelpers;
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
        Log::info($stripePayment->object);

        $customerId = trim($stripePayment->customer);

        CustomerPaymentEvent::create([
            'event_id' => $stripePayment->id,
            'object_type' => $stripePayment->object,//charge, invoice,payment_intent
            'status' => $stripePayment->status,
            'customer' => $stripePayment->customer,
            'event_details' => json_encode($stripePayment),
        ]);

        $customerPayment = CustomerPayment::where('customer_id', $customerId)
            ->first();

        if (!$customerPayment) {
            CustomerPayment::create([
                'customer_id' => $stripePayment->customer,
                'status' => $stripePayment->status,
            ]);
        } else {
            switch ($stripePayment->object){
                case "charge":
                    $customerPayment->charge_id = $stripePayment->id;
                    break;
                case "invoice":
                    $customerPayment->invoice_id =  $stripePayment->id;
                    $customerPayment->amount =  $stripePayment->amount_paid;
                    $customerPayment->customer_email =  $stripePayment->customer_email;
                    $customerPayment->customer_name = $stripePayment->customer_name;
                    $customerPayment->country = $stripePayment->customer_address->country;
                    break;
                case "payment_intent":
                    $customerPayment->payment_intent_id = $stripePayment->id;
                    $customerPayment->description = $stripePayment->description;
                    break;
            }
        }

        //check if customer subscription exists, if not create one, get and update user
        //else update details
        //if object - payment_intent.succeeded - update payment_intent_id,amount,customer,description,invoice,status
        //if object - charge.succeeded - charge_id
        //if object -invoice.payment_succeeded - customer_email, customer_name, customer_address.country, send email if subscription was updated

        $existingSubscription = CustomerSubscription::where('customer_id', $customerId)
            ->first();

        if (!$existingSubscription) {
            PaymentHelpers::createANewCustomerSubscription($customerId, "$-paymentIntentId", "$-uniqueInvoice");
        } else {
            switch ($stripePayment->object) {
                case "payment_intent":
                    if ($stripePayment->description == "Subscription update") {
                        $user = User::where('customer_id', $customerId)->first();
                        $uniqueInvoice = PaymentHelpers::generateUniqueInvoice($user->username);
                        $existingSubscription->invoice = $uniqueInvoice;
                        $details = [
                            'type' => EmailTypes::SUBSCRIPTION_RENEWAL_CONFIRMATION->name,
                            'recipientEmail' => trim($user->email),
                            'username' => trim($user->username),
                            'invoice' => trim($uniqueInvoice),
                        ];

                        DispatchEmailNotificationsJob::dispatch($details);
                    }
                    break;
                case "charge":
                    $customerPayment->charge_id = $stripePayment->id;
                    break;
                case "invoice":
                    $existingSubscription->payment_intent_id = $stripePayment->payment_intent;
                    break;
            }
        }

        $customerPayment->save();
        $existingSubscription->save();

        //Send a receipt email to the customer only when subscription has been created
        if ($stripePayment->object == "invoice" && $stripePayment->billing_reason == "subscription_create") {
            $details = [
                'type' => EmailTypes::PAYMENT_CHECKOUT_RECEIPT->name,
                'recipientEmail' => trim($stripePayment->customer_email),
                'username' => trim($stripePayment->customer_name),
            ];

            DispatchEmailNotificationsJob::dispatch($details);
        }
    }
}
