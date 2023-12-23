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
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->string('stripe_session_id');
            $table->string('stripe_subscription');
            $table->string('stripe_customer');
            $table->dateTime('stripe_created');
            $table->dateTime('stripe_expires_at');
            $table->string('stripe_payment_status');
            $table->string('stripe_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('stripe_session_id');
            $table->dropColumn('stripe_subscription');
            $table->dropColumn('stripe_customer');
            $table->dropColumn('stripe_created');
            $table->dropColumn('stripe_expires_at');
            $table->dropColumn('stripe_payment_status');
            $table->dropColumn('stripe_status');
        });
    }
};
