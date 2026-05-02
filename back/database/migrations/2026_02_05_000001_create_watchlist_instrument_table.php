<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot for watchlist <-> instrument. `position` keeps the user-defined
 * ordering stable; pinning will sort high-priority entries first within
 * the same watchlist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist_instrument', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchlist_id')->constrained('watchlists')->onDelete('cascade');
            $table->foreignId('instrument_id')->constrained('instruments')->onDelete('cascade');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['watchlist_id', 'instrument_id']);
            $table->index(['watchlist_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_instrument');
    }
};
