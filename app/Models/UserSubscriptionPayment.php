<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptionPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'session_id',
        'session_created',
        'session_expires_at',
        'customer',
        'customer_details',
        'invoice',
        'payment_status',
        'subscription',
    ];
}
