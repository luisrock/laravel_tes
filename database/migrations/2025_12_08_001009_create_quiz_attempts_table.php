<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();
            
            // Identificação do visitante (user_id se logado, session_id se anônimo)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('session_id', 100)->nullable(); // Para visitantes anônimos
            
            // Dados da tentativa
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            
            // Resultados
            $table->unsignedInteger('score')->default(0); // Número de acertos
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('time_spent_seconds')->nullable(); // Tempo total gasto
            
            // Status
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            
            $table->timestamps();
            
            // Índices
            $table->index(['quiz_id', 'status']);
            $table->index(['session_id']);
            $table->index(['user_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_attempts');
    }
}
