<?php

declare(strict_types=1);

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
        Schema::create('store_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('event_name');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->json('context')->nullable();
            $table->dateTime('occurred_at');

            $table->index('event_name');
            $table->index('occurred_at');
            $table->index(['user_id', 'event_name']);
            $table->index(['product_id', 'event_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_analytics_events');
    }
};
