<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAiContentFieldsToPesquisasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesquisas', function (Blueprint $table) {
            // add column
            $table->text('ai_prompt')->nullable()->after('tribunal');
            $table->text('ai_answer')->nullable()->after('ai_prompt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesquisas', function (Blueprint $table) {
            // undo
            $table->dropColumn('ai_prompt');
            $table->dropColumn('ai_answer');
        });
    }
}
