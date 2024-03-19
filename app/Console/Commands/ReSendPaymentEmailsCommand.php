<?php

namespace App\Console\Commands;

use App\Jobs\DispatchEmailNotificationsJob;
use App\Models\PaymentReceiptEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReSendPaymentEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:resend-stuck-payment-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to re-send stuck payment emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stuckEmails = PaymentReceiptEmail::where('is_sent', 0)->get();
        if ($stuckEmails) {
            foreach ($stuckEmails as $stuckEmail) {
                $jsonString = json_decode($stuckEmail->payload);
                $emailDetails = [
                    'type' => $jsonString->type,
                    'recipientEmail' => $jsonString->recipientEmail,
                    'username' => $jsonString->username,
                ];
                DispatchEmailNotificationsJob::dispatch($emailDetails);

                $receiptEmail = PaymentReceiptEmail::find($stuckEmail->id);
                $receiptEmail->update([
                    'is_sent' => 1
                ]);
            }
            $this->info("Stuck payment emails resent successfully");
        }
    }
}
