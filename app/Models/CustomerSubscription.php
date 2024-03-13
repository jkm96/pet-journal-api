<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'payment_intent_id',
        'subscription_plan_id',
        'invoice',
        'start_date',
        'end_date',
        'period_start',
        'period_end',
        'status',
    ];

    /**
     * Get the user that owns the CustomerSubscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id','customer_id');
    }

    /**
     * Get the SubscriptionPlan associated with the CustomerSubscription.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
