<?php

namespace App\Models;

use App\Events\PaymentEmailSavedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PaymentReceiptEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_object',
        'payment_object_id',
        'payment_object_created',
        'email_type',
        'recipient_email',
        'payload',
        'is_sent'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            // Check if the model hasn't been sent yet
            if (!$model->is_sent) {
                PaymentEmailSavedEvent::dispatch($model);
            }
        });
    }
}
