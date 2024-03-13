<?php

namespace App\Providers;

use App\Events\PaymentEmailSavedEvent;
use App\Listeners\PaymentCheckoutListener;
use App\Listeners\PaymentEmailSavedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'stripe-webhooks::checkout.session.completed' => [
            PaymentCheckoutListener::class,
        ],
        'stripe-webhooks::checkout.session.async_payment_failed' => [
            PaymentCheckoutListener::class,
        ],
        'stripe-webhooks::payment_intent.created' => [
            PaymentCheckoutListener::class,
        ],
        'stripe-webhooks::payment_intent.succeeded' => [
            PaymentCheckoutListener::class,
        ],
        'stripe-webhooks::invoice.payment_succeeded' => [
            PaymentCheckoutListener::class,
        ],
        PaymentEmailSavedEvent::class => [
            PaymentEmailSavedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
