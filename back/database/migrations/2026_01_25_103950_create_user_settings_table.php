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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('theme')->default('light');
            $table->string('language', 5)->default('en');
            $table->string('timezone')->default('UTC');
            $table->string('default_chart_range')->default('1M');
            $table->string('default_chart_interval')->default('1d');
            $table->boolean('show_extended_metrics')->default(false);
            $table->boolean('notifications_enabled')->default(true);
            $table->json('preferences')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
