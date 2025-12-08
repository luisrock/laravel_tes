<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Inserir configuração de visibilidade dos quizzes na home
        DB::table('editable_contents')->insert([
            'slug' => 'quizzes-home-visibility',
            'title' => 'Exibir Quizzes na Home',
            'meta_description' => 'Controla se a seção de quizzes aparece na página inicial',
            'content' => '',
            'published' => false, // Começa oculto
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('editable_contents')
            ->where('slug', 'quizzes-home-visibility')
            ->delete();
    }
};
