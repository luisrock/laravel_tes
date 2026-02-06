<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeseAnalysisJobsTable extends Migration
{
    public function up()
    {
        Schema::create('tese_analysis_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tese_id');
            $table->enum('tribunal', ['STF', 'STJ']);
            $table->enum('section_type', [
                'all',
                'caso_fatico',
                'contornos_juridicos',
                'modulacao',
                'tese_explicada',
                'teaser'
            ])->default('all');

            $table->unsignedBigInteger('ai_model_id');

            $table->enum('status', ['queued', 'running', 'done', 'error'])->default('queued');
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->text('last_error')->nullable();
            $table->string('locked_by', 50)->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Foreign key
            $table->foreign('ai_model_id')->references('id')->on('ai_models');

            // Índices
            $table->unique(['tese_id', 'tribunal', 'section_type'], 'unique_job');
            $table->index(['status', 'created_at'], 'idx_status_created');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tese_analysis_jobs');
    }
}
