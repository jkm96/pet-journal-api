<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'object_type',
        'status',
        'customer',
        'event_details',
    ];
}
