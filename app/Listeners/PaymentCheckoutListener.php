<?php

namespace App\Listeners;

use App\Jobs\DispatchEmailNotificationsJob;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentEvent;
use App\Models\CustomerSubscription;
use App\Models\PaymentReceiptEmail;
use App\Models\User;
use App\Utils\Enums\EmailTypes;
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
//        Log::info($stripePayment);
        Log::info($stripePayment->object);

        $customerId = trim($stripePayment->customer);
        Log::info("CustomerId " . $customerId);
        Log::info("creating CustomerPaymentEvent");
        CustomerPaymentEvent::create([
            'event_id' => $stripePayment->id,
            'object_type' => $stripePayment->object,//charge, invoice,payment_intent
            'status' => $stripePayment->status,
            'customer' => $stripePayment->customer,
            'event_details' => json_encode($stripePayment),
        ]);

        //check if customer subscription exists, if not create one, get and update user
        //else update details
        //if object - payment_intent.succeeded - update payment_intent_id,amount,customer,description,invoice,status
        //if object - charge.succeeded - charge_id
        //if object -invoice.payment_succeeded - customer_email, customer_name, customer_address.country, send email if subscription was updated

        //Process CustomerPayment
        $customerPaymentData = [
            'customer_id' => $stripePayment->customer,
            'status' => $stripePayment->status,
        ];

        switch ($stripePayment->object) {
            case "invoice":
                $customerPaymentData += [
                    'charge_id' => $stripePayment->charge,
                    'invoice_id' => $stripePayment->id,
                    'amount' => $stripePayment->amount_paid,
                    'customer_email' => $stripePayment->customer_email,
                    'customer_name' => $stripePayment->customer_name,
                    'country' => $stripePayment->customer_address->country,
                ];
                break;
            case "payment_intent":
                $customerPaymentData += [
                    'payment_intent_id' => $stripePayment->id,
                    'description' => $stripePayment->description,
                ];
                break;
        }

        CustomerPayment::updateOrCreate(
            ['customer_id' => $customerId],
            $customerPaymentData
        );

        //Send a receipt email to the customer only when subscription has been created-subscription_create/updated - subscription_cycle
        if ($stripePayment->object == "invoice") {
            switch ($stripePayment->billing_reason) {
                case "subscription_create":
                    //Process CustomerSubscription
                    $this->processCustomerSubscription($stripePayment, $customerId);

                    Log::info("sending PAYMENT_CHECKOUT_RECEIPT email");
                    //save email to table to prevent sending duplicates
                    //object,object_id,email_type,recipient_email
                    $recipientEmail = trim($stripePayment->customer_email);
                    $emailType = EmailTypes::PAYMENT_CHECKOUT_RECEIPT->name;
                    $emailDetails = [
                        'type' => $emailType,
                        'recipientEmail' => $recipientEmail,
                        'username' => trim($stripePayment->customer_name),
                    ];

                    $this->savePaymentEmail($recipientEmail, $stripePayment, $emailType, $emailDetails);
                    break;
                case "subscription_cycle":
                    Log::info("sending SUBSCRIPTION_RENEWAL_CONFIRMATION email");
                    $user = User::where('customer_id', $customerId)->first();
                    $uniqueInvoice = PaymentHelpers::generateUniqueInvoice($user->username);
                    $recipientEmail =  trim($user->email);
                    $emailType = EmailTypes::SUBSCRIPTION_RENEWAL_CONFIRMATION->name;
                    //TODO handle customer subscription renewal
                    $emailDetails = [
                        'type' => $emailType,
                        'recipientEmail' => $recipientEmail,
                        'username' => trim($user->username),
                        'invoice' => trim($uniqueInvoice),
                    ];

                    $this->savePaymentEmail($recipientEmail, $stripePayment, $emailType, $emailDetails);
                    break;
            }
        }
    }

    /**
     * @param mixed $stripePayment
     * @param string $customerId
     * @return void
     */
    public function processCustomerSubscription(mixed $stripePayment, string $customerId): void
    {
        $subscriptionData = [
            'payment_intent_id' => $stripePayment->payment_intent,
            'period_start' => $stripePayment->period_start,
            'period_end' => $stripePayment->period_end,
        ];

        $existingSubscription = CustomerSubscription::where('customer_id', $customerId)
            ->first();
        if (!$existingSubscription) {
            Log::info("creating CustomerSubscription");
            PaymentHelpers::createANewCustomerSubscription($customerId, "$-paymentIntentId", "$-uniqueInvoice");
        } else {
            $existingSubscription->update($subscriptionData);
        }
    }

    /**
     * @param $recipientEmail
     * @param $stripePayment
     * @param $emailType
     * @param $details
     * @return void
     */
    public function savePaymentEmail($recipientEmail, $stripePayment, $emailType, $details): void
    {
        $existingPaymentEmail = PaymentReceiptEmail::where('recipient_email', $recipientEmail)
            ->where('payment_object_id', $stripePayment->id)
            ->where('payment_object_created', $stripePayment->created)
            ->first();
        if (!$existingPaymentEmail) {
            PaymentReceiptEmail::create([
                'payment_object' => $stripePayment->object,
                'payment_object_id' => $stripePayment->id,
                'payment_object_created' => $stripePayment->created,
                'email_type' => $emailType,
                'recipient_email' => $recipientEmail,
                'payload' => json_encode($details),
            ]);
        }
    }
}
