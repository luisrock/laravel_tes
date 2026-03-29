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
        Schema::create('collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 20); // tese, sumula — futuro: quiz
            $table->unsignedBigInteger('content_id');
            $table->string('tribunal', 10);
            $table->unsignedInteger('order')->default(0);
            $table->text('notes')->nullable(); // reservado para feature futura de anotações
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['collection_id', 'content_type', 'content_id', 'tribunal'], 'collection_items_unique');
            $table->index(['collection_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_items');
    }
};
