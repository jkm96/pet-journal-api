<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'invoice',
        'status',
        'stripe_session_id',
        'stripe_subscription',
        'stripe_customer',
        'stripe_created',
        'stripe_expires_at',
        'stripe_payment_status',
        'stripe_status',
    ];

    /**
     * Get the user that owns the UserSubscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the SubscriptionPlan associated with the UserSubscription.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
