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
        Schema::create('magic_studio_projects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('slug');
            $table->string('title');
            $table->longText('content');
            $table->date('period_from');
            $table->date('period_to');
            $table->timestamps();

            $table->unique(['id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magic_studios');
    }
};
