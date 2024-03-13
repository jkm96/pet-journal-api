<?php

namespace App\Listeners;

use App\Events\PaymentEmailSavedEvent;
use App\Jobs\DispatchEmailNotificationsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PaymentEmailSavedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 10;

    /**
     * Handle the event.
     */
    public function handle(PaymentEmailSavedEvent $event)
    {
        $paymentEmailModel = $event->paymentReceiptEmail;
        if (!$paymentEmailModel->is_sent) {
            $jsonString = json_decode($paymentEmailModel->payload);
            $emailDetails = [
                'type' => $jsonString->type,
                'recipientEmail' => $jsonString->recipientEmail,
                'username' => $jsonString->username,
            ];
            DispatchEmailNotificationsJob::dispatch($emailDetails);
            $paymentEmailModel->is_sent = 1;
            $paymentEmailModel->save();
        }

        return false;
    }
}
