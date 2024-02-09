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
        Schema::table('magic_studio_projects', function (Blueprint $table) {
            $table->dropColumn("content");
            $table->text('pdf_content')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('magic_studio_projects', function (Blueprint $table) {
            $table->string("content")->nullable();
            $table->longText("pdf_content");
        });
    }
};
