<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inclui o provider "openrouter" no enum de ai_models.provider, preservando os existentes.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ai_models MODIFY COLUMN provider ENUM('openai', 'anthropic', 'google', 'openrouter') NOT NULL");

            return;
        }

        Schema::table('ai_models', function (Blueprint $table) {
            $table->enum('provider', ['openai', 'anthropic', 'google', 'openrouter'])->change();
        });
    }

    /**
     * Reverte o enum para os providers originais.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ai_models MODIFY COLUMN provider ENUM('openai', 'anthropic', 'google') NOT NULL");

            return;
        }

        Schema::table('ai_models', function (Blueprint $table) {
            $table->enum('provider', ['openai', 'anthropic', 'google'])->change();
        });
    }
};
