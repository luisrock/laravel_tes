<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateQuizCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Seed categorias padrão
        DB::table('quiz_categories')->insert([
            ['name' => 'Direito Tributário', 'slug' => 'tributario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Constitucional', 'slug' => 'constitucional', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Administrativo', 'slug' => 'administrativo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Civil', 'slug' => 'civil', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Penal', 'slug' => 'penal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito do Trabalho', 'slug' => 'trabalhista', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Previdenciário', 'slug' => 'previdenciario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Processual Civil', 'slug' => 'processual-civil', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Processual Penal', 'slug' => 'processual-penal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Ambiental', 'slug' => 'ambiental', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito Empresarial', 'slug' => 'empresarial', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direito do Consumidor', 'slug' => 'consumidor', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_categories');
    }
}
