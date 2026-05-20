<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscription_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->index();
            $table->string('action', 32);
            $table->string('source', 32);
            $table->string('popup_variant', 8)->nullable();
            $table->string('popup_trigger', 16)->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('referrer', 1024)->nullable();
            $table->string('page_url', 512)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['action', 'source', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscription_events');
    }
};
