<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabelas legadas de jurisprudência (teses/súmulas por tribunal) não fazem parte
 * do histórico de migrations do app em produção (já existem na DB real).
 *
 * Em ambiente de testes com MySQL vazio (ex.: docker), criamos um esquema mínimo
 * para a suíte tests/MySQL. Em SQLite (RefreshDatabase nos Feature tests) esta
 * migration é ignorada. Se stf_teses já existir no MySQL, assume-se produção e
 * esta migration não faz nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('stf_teses')) {
            return;
        }

        Schema::create('stf_teses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->text('tema_texto')->nullable();
            $table->text('tese_texto')->nullable();
            $table->string('situacao')->nullable();
            $table->string('relator')->nullable();
            $table->string('aprovadaEm')->nullable();
            $table->string('acordao')->nullable();
            $table->string('link')->nullable();
        });

        Schema::create('stj_teses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->string('orgao')->nullable();
            $table->text('tema')->nullable();
            $table->text('tese_texto')->nullable();
            $table->string('ramos')->nullable();
            $table->string('atualizadaEm')->nullable();
            $table->string('situacao')->nullable();
            $table->fullText(['tese_texto', 'tema', 'ramos'], 'stj_teses_fulltext');
        });

        Schema::create('tst_teses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->string('titulo')->nullable();
            $table->text('tema')->nullable();
            $table->text('texto')->nullable();
            $table->string('tipo')->nullable();
            $table->string('link')->nullable();
        });

        Schema::create('tnu_teses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->string('titulo')->nullable();
            $table->string('ramo')->nullable();
            $table->text('tema')->nullable();
            $table->text('tese')->nullable();
            $table->string('relator')->nullable();
            $table->string('processo')->nullable();
            $table->string('situacao')->nullable();
            $table->string('link')->nullable();
            $table->string('julgadoEm')->nullable();
            $table->string('publicadoEm')->nullable();
        });

        Schema::create('stf_sumulas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->string('titulo')->nullable();
            $table->text('texto')->nullable();
            $table->text('obs')->nullable();
            $table->text('legis')->nullable();
            $table->text('precedentes')->nullable();
            $table->boolean('is_vinculante')->default(false);
            $table->string('aprovadaEm')->nullable();
            $table->string('link')->nullable();
            $table->unsignedInteger('seq')->nullable();
        });

        Schema::create('stj_sumulas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->string('titulo')->nullable();
            $table->text('texto')->nullable();
            $table->text('texto_raw')->nullable();
            $table->string('ramos')->nullable();
            $table->string('publicadaEm')->nullable();
            $table->boolean('isCancelada')->default(false);
            $table->fullText(['texto_raw', 'ramos'], 'stj_sumulas_fulltext');
        });

        Schema::create('tst_sumulas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->string('titulo')->nullable();
            $table->text('tema')->nullable();
            $table->text('texto')->nullable();
            $table->string('link')->nullable();
        });

        Schema::create('tnu_sumulas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->index();
            $table->string('titulo')->nullable();
            $table->text('texto')->nullable();
            $table->text('dados')->nullable();
            $table->string('link')->nullable();
            $table->boolean('isCancelada')->default(false);
        });
    }

    /**
     * Não remove tabelas legadas em rollback: em produção up() é normalmente no-op
     * e estas tabelas contêm dados de domínio.
     */
    public function down(): void {}
};
