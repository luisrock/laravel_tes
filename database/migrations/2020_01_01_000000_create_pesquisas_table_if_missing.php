<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesquisasTableIfMissing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pesquisas')) {
            return;
        }

        Schema::create('pesquisas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('keyword')->nullable();
            $table->string('label')->nullable();
            $table->string('slug')->nullable();
            $table->string('tribunal')->nullable();
            $table->text('concept')->nullable();
            $table->timestamp('concept_validated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('pesquisas')) {
            return;
        }

        Schema::drop('pesquisas');
    }
}
