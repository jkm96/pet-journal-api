<?php

namespace App\Jobs;

use App\Mail\PaymentCheckoutConfirmationMail;
use App\Mail\PaymentCheckoutReceiptMail;
use App\Mail\UserVerificationMail;
use App\Utils\Enums\EmailTypes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DispatchEmailNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $jodPayload;

    /**
     * Create a new job instance.
     */
    public function __construct($jodPayload)
    {
        $this->jodPayload = $jodPayload;
    }

    /**
     * Execute the job.
     */
    public function handle($emailData): void
    {
        try {
            $email = null;
            switch ($emailData['type']) {
                case EmailTypes::USER_VERIFICATION->name:
                    $email = new UserVerificationMail($emailData);
                    break;
                case EmailTypes::PAYMENT_CHECKOUT_RECEIPT->name:
                    $email = new PaymentCheckoutReceiptMail($emailData);
                    break;
                case EmailTypes::PAYMENT_CHECKOUT_CONFIRMATION->name:
                    $email = new PaymentCheckoutConfirmationMail($emailData);
                    break;
            }
            Mail::to($emailData['recipientEmail'])->send($email);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
        }
    }
}
