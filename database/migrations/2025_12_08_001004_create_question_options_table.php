<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            $table->char('letter', 1); // A, B, C, D, E
            $table->text('text'); // Texto da alternativa
            $table->boolean('is_correct')->default(false);

            $table->timestamps();

            // Índices
            $table->index(['question_id', 'letter']);
            $table->unique(['question_id', 'letter']); // Uma letra por questão
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_options');
    }
}
