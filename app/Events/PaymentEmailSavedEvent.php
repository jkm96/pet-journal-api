<?php

namespace App\Events;

use App\Models\PaymentReceiptEmail;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentEmailSavedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $paymentReceiptEmail;

    /**
     * Create a new event instance.
     * @param PaymentReceiptEmail $paymentReceiptEmail
     */
    public function __construct(PaymentReceiptEmail $paymentReceiptEmail)
    {
        $this->paymentReceiptEmail = $paymentReceiptEmail;
    }
}
