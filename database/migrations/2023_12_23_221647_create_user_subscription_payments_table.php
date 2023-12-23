<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->dateTime('session_created');
            $table->dateTime('session_expires_at');
            $table->string('customer');
            $table->json('customer_details');
            $table->string('invoice');
            $table->string('payment_status');
            $table->string('subscription');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscription_payments');
    }
};
