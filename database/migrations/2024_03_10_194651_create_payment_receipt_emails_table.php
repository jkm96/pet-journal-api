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
        Schema::create('payment_receipt_emails', function (Blueprint $table) {
            $table->id();
            $table->string('payment_object');
            $table->string('payment_object_id');
            $table->string('payment_object_created');
            $table->string('email_type');
            $table->string('recipient_email');
            $table->json('payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipt_emails');
    }
};
