<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Watchlists belong to a user. A user can have many watchlists; each
 * watchlist holds many instruments via the pivot table created in the
 * companion migration.
 *
 * is_default flags the row that the SPA opens by default — every user
 * has exactly one default watchlist, enforced at the service layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
