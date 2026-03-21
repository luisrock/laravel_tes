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
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 20);
            $table->unsignedBigInteger('content_id');
            $table->string('tribunal', 10);
            $table->timestamp('viewed_at');

            $table->index(['user_id', 'content_type', 'content_id', 'tribunal'], 'content_views_uniqueness');
            $table->index(['user_id', 'viewed_at'], 'content_views_daily_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
