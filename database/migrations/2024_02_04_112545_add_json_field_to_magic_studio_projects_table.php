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
        Schema::table('magic_studio_projects', function (Blueprint $table) {
            $table->json("pdf_content");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('magic_studio_projects', function (Blueprint $table) {
            $table->dropColumn("pdf_content");
        });
    }
};
