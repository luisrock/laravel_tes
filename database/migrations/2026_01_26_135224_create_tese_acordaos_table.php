<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeseAcordaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tese_acordaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tese_id');
            $table->enum('tribunal', ['STF', 'STJ']);
            $table->string('numero_acordao', 100);        // Ex: "RE 559937"
            $table->enum('tipo', [
                'Principal',
                'Embargos de Declaração',
                'Modulação de Efeitos',
                'Recurso Extraordinário',
                'Recurso Especial',
                'Outros',
            ])->default('Principal');
            $table->string('label', 255)->nullable();     // Descrição livre
            $table->string('s3_key', 500);                // Caminho no S3
            $table->string('filename_original', 255);     // Nome original do arquivo
            $table->unsignedInteger('file_size')->nullable(); // Tamanho em bytes
            $table->string('mime_type', 100)->default('application/pdf'); // MIME type validado
            $table->string('checksum', 64)->nullable();   // SHA-256 para detecção de duplicatas
            $table->unsignedInteger('version')->default(1); // Versionamento do mesmo acórdão
            $table->unsignedBigInteger('uploaded_by')->nullable(); // user_id do admin que fez upload
            $table->string('upload_ip', 45)->nullable(); // IP do upload (suporta IPv6)
            $table->softDeletes();                         // Soft delete para auditoria
            $table->unsignedBigInteger('deleted_by')->nullable(); // user_id que deletou
            $table->timestamps();

            // Índices para performance
            $table->index(['tribunal', 'tese_id']);
            $table->index(['tese_id', 'tribunal', 'numero_acordao']); // Busca rápida
            $table->index('checksum'); // Detecção de duplicatas
            $table->index('uploaded_by'); // Auditoria
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tese_acordaos');
    }
}
