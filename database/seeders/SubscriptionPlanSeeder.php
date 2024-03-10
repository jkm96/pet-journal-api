<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SubscriptionPlan::count() === 0) {
            SubscriptionPlan::create([
                'name' => 'Premium Plan',
                'description' => 'Premium subscription plan',
                'price' => 4.00,
                'billing_cycle' => 'monthly',
            ]);
            $this->command->info('Subscription plan created successfully!');
        } else {
            $this->command->info('Subscription plan already exists, skipping creation.');
        }
    }
}
