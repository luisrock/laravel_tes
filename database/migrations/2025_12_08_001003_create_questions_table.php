<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('text'); // Enunciado da questão
            $table->text('explanation')->nullable(); // Explicação da resposta correta
            
            // Categoria
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('quiz_categories')
                ->nullOnDelete();
            
            // Dificuldade
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            
            // Estatísticas
            $table->unsignedInteger('times_answered')->default(0);
            $table->unsignedInteger('times_correct')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index(['category_id']);
            $table->index(['difficulty']);
            $table->fullText(['text']); // Busca full-text no enunciado
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
