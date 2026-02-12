<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTeseAnalysisSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('tese_analysis_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tese_id');
            $table->enum('tribunal', ['STF', 'STJ']);
            $table->enum('section_type', [
                'caso_fatico',
                'contornos_juridicos',
                'modulacao',
                'tese_explicada',
                'teaser',
            ]);

            // Conteúdo
            $table->mediumText('content');
            $table->enum('status', ['draft', 'reviewed', 'published'])->default('draft');
            $table->boolean('is_active')->default(false);

            // Modelo e prompt (rastreabilidade)
            $table->unsignedBigInteger('ai_model_id');
            $table->string('prompt_key', 100)->nullable();
            $table->char('prompt_hash', 64)->nullable();
            $table->char('source_hash', 64)->nullable();

            // Tokens e custo
            $table->integer('tokens_input')->nullable();
            $table->integer('tokens_output')->nullable();
            $table->decimal('cost_usd', 10, 6)->nullable();

            // Snapshot de preço (para auditoria histórica)
            $table->decimal('price_snapshot_input', 10, 4)->nullable();
            $table->decimal('price_snapshot_output', 10, 4)->nullable();

            // Metadados de execução
            $table->string('provider_request_id', 100)->nullable();
            $table->integer('latency_ms')->nullable();
            $table->string('finish_reason', 30)->nullable();
            $table->json('raw_usage')->nullable();

            // Erro (se falhou)
            $table->text('error_message')->nullable();

            // Auditoria
            $table->timestamp('generated_at')->useCurrent();
            $table->unsignedBigInteger('activated_by')->nullable();
            $table->timestamp('activated_at')->nullable();

            // Foreign keys
            $table->foreign('ai_model_id')->references('id')->on('ai_models');

            // Índices
            $table->index(['tese_id', 'tribunal', 'section_type'], 'idx_tese_section');
            $table->index(['tese_id', 'tribunal', 'section_type', 'generated_at'], 'idx_section_history');
        });

        // Adicionar coluna gerada via SQL raw (apenas MySQL — SQLite não suporta)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE tese_analysis_sections
                ADD COLUMN active_key VARCHAR(500) GENERATED ALWAYS AS (
                    IF(is_active, CONCAT(tese_id, ':', tribunal, ':', section_type), NULL)
                ) STORED AFTER is_active
            ");

            // Índice único na coluna gerada
            DB::statement('
                ALTER TABLE tese_analysis_sections
                ADD UNIQUE KEY uniq_active_key (active_key)
            ');
        }
    }

    public function down()
    {
        Schema::dropIfExists('tese_analysis_sections');
    }
}
