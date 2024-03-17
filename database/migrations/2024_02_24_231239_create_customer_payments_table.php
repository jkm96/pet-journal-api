<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id');
            $table->string('payment_intent_id')->nullable();
            $table->string('invoice_id')->nullable();
            $table->string('charge_id')->nullable();
            $table->string('amount')->default("0.00");
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('country')->nullable();
            $table->string('description')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
