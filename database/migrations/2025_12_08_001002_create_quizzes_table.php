<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Tribunal e tema relacionado
            $table->string('tribunal', 10)->nullable(); // STF, STJ, TST, TNU, etc.
            $table->integer('tema_number')->nullable(); // Número do tema (opcional)
            
            // Categoria
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('quiz_categories')
                ->nullOnDelete();
            
            // Configurações
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('estimated_time')->default(2); // em minutos
            $table->string('color', 7)->default('#912F56'); // Cor primária do quiz
            
            // Opções de exibição
            $table->boolean('show_ads')->default(true);
            $table->boolean('show_share')->default(true);
            $table->boolean('show_progress')->default(true);
            $table->boolean('random_order')->default(false);
            $table->boolean('show_feedback_immediately')->default(true); // false = mostrar só no final
            
            // SEO
            $table->string('meta_keywords')->nullable();
            
            // Status e estatísticas
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('views_count')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index(['status', 'created_at']);
            $table->index(['tribunal', 'status']);
            $table->index(['category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quizzes');
    }
}
