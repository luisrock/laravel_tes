<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizTeseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_tese', function (Blueprint $table) {
            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('pesquisa_id'); // FK para tabela pesquisas

            $table->primary(['quiz_id', 'pesquisa_id']);

            // Nota: Não criamos foreign key para pesquisas pois a tabela
            // pode não existir no momento da migration ou ter estrutura diferente.
            // O relacionamento será gerenciado pela aplicação.
            $table->index(['pesquisa_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_tese');
    }
}
