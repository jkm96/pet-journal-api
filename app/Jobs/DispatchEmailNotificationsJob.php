<?php

namespace App\Jobs;

use App\Mail\UserVerificationMail;
use App\Utils\Enums\EmailTypes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    public function handle(): void
    {
        $emailData = $this->jodPayload;
        switch ($emailData['type']) {
            case EmailTypes::USER_VERIFICATION->name:
                $email = new UserVerificationMail($emailData);
                Mail::to($emailData['recipientEmail'])->send($email);
                break;
        }
    }
}
