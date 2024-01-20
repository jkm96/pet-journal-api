<?php

namespace App\Jobs;

use App\Mail\PaymentCheckoutConfirmationMail;
use App\Mail\PaymentCheckoutReceiptMail;
use App\Mail\UserVerificationMail;
use App\Utils\Enums\EmailTypes;
use Exception;
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

    public $jobPayload;

    /**
     * Create a new job instance.
     */
    public function __construct($jobPayload)
    {
        $this->jobPayload = $jobPayload;
    }

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $emailData = $this->jobPayload;
        try {
            Log::info($emailData);
            Log::info("Email type ".$emailData['type']);
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
            Log::info("sent email to ".$emailData['recipientEmail']);
            Mail::to($emailData['recipientEmail'])->send($email);
        } catch (Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
        }
    }
}
