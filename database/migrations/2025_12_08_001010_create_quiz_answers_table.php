<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('attempt_id')
                ->constrained('quiz_attempts')
                ->cascadeOnDelete();
            
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();
            
            $table->foreignId('selected_option_id')
                ->nullable()
                ->constrained('question_options')
                ->nullOnDelete();
            
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('time_spent_seconds')->nullable(); // Tempo gasto nesta pergunta
            
            $table->timestamp('answered_at')->useCurrent();
            
            // Índices
            $table->unique(['attempt_id', 'question_id']); // Uma resposta por pergunta por tentativa
            $table->index(['question_id', 'is_correct']); // Para estatísticas por pergunta
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_answers');
    }
}
