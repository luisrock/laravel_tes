<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiModelsTable extends Migration
{
    public function up()
    {
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->enum('provider', ['openai', 'anthropic', 'google']);
            $table->string('name', 100);
            $table->string('model_id', 100);
            $table->decimal('price_input_per_million', 10, 4)->nullable();
            $table->decimal('price_output_per_million', 10, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('deprecated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['provider', 'model_id'], 'unique_model');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_models');
    }
}
