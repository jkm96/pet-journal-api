<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    /**
     * Get the SubscriptionPlan associated with the UserSubscription.
     */
    public function subscriptionPlan()
    {
        return $this->hasOne(SubscriptionPlan::class);
    }
}
