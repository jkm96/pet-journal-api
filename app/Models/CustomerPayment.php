<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'payment_intent_id',
        'invoice_id',
        'charge_id',
        'amount',
        'customer_email',
        'customer_name',
        'country',
        'description',
        'status',
    ];
}
