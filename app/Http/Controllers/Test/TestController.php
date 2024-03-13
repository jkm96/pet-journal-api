<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceiptEmail;
use App\Utils\Enums\EmailTypes;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function performTest(Request $request)
    {
//        dd($request);
        $recipientEmail = 'jackhunter@info.com';
        $emailType = EmailTypes::PAYMENT_CHECKOUT_RECEIPT->name;
        $stripePaymentCreated = 128798;
        $stripePaymentId = "stripeId123456";
        $stripePaymentObject = "invoice";

        $emailDetails = [
            'type' => $emailType,
            'recipientEmail' => $recipientEmail,
            'username' => 'jack',
        ];
        $existingPaymentEmail = PaymentReceiptEmail::where('recipient_email', $recipientEmail)
            ->where('payment_object_id',$stripePaymentId)
            ->where('payment_object_created', $stripePaymentCreated)
            ->first();
        if (!$existingPaymentEmail) {
            PaymentReceiptEmail::create([
                'payment_object' => $stripePaymentObject,
                'payment_object_id' => $stripePaymentId,
                'payment_object_created' => $stripePaymentCreated,
                'email_type' => $emailType,
                'recipient_email' => $recipientEmail,
                'payload' => json_encode($emailDetails),
            ]);
        }
    }
}
