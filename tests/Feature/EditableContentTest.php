<?php

use App\Models\EditableContent;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Testes do fluxo de EditableContent — páginas públicas e edição admin.
 *
 * NOTA: A rota pública GET /{slug} só aceita o slug 'precedentes-vinculantes-cpc'
 * devido ao constraint ->where('slug', 'precedentes-vinculantes-cpc') no web.php.
 * A edição admin usa rotas separadas com middleware admin_access.
 */

// ==========================================
// Página Pública
// ==========================================

describe('Página Pública de Conteúdo', function () {

    it('exibe conteúdo publicado com slug válido', function () {
        EditableContent::create([
            'slug' => 'precedentes-vinculantes-cpc',
            'title' => 'Precedentes Vinculantes no CPC',
            'content' => '<p>Conteúdo sobre precedentes vinculantes</p>',
            'published' => true,
        ]);

        $this->get('/precedentes-vinculantes-cpc')
            ->assertStatus(200)
            ->assertSee('Precedentes Vinculantes no CPC');
    });

    it('retorna 404 para slug inexistente', function () {
        $this->get('/slug-que-nao-existe-xyz')
            ->assertNotFound();
    });

    it('retorna 404 para conteúdo não publicado', function () {
        EditableContent::create([
            'slug' => 'precedentes-vinculantes-cpc',
            'title' => 'Rascunho',
            'content' => '<p>Conteúdo não publicado</p>',
            'published' => false,
        ]);

        $this->get('/precedentes-vinculantes-cpc')
            ->assertNotFound();
    });

});

// ==========================================
// Edição Admin
// ==========================================

describe('Edição de Conteúdo (Admin)', function () {

    it('permite edição para admin com email autorizado', function () {
        Config::set('tes_constants.admins', ['admin@test.com']);

        $admin = createAdminUser();
        $admin->update(['email' => 'admin@test.com']);

        DB::table('editable_contents')->insert([
            'slug' => 'pagina-teste',
            'title' => 'Página de Teste',
            'content' => '<p>Conteúdo</p>',
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/content/pagina-teste/edit')
            ->assertStatus(200);
    });

    it('retorna 403 para usuário comum na edição', function () {
        $user = User::factory()->create();

        DB::table('editable_contents')->insert([
            'slug' => 'pagina-teste-403',
            'title' => 'Página',
            'content' => '<p>Conteúdo</p>',
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/admin/content/pagina-teste-403/edit')
            ->assertForbidden();
    });

    it('valida campos obrigatórios no update', function () {
        Config::set('tes_constants.admins', ['admin@test.com']);

        $admin = createAdminUser();
        $admin->update(['email' => 'admin@test.com']);

        DB::table('editable_contents')->insert([
            'slug' => 'pagina-validacao',
            'title' => 'Título Original',
            'content' => '<p>Conteúdo original</p>',
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put('/admin/content/pagina-validacao', [
                'title' => '',
                'content' => '',
            ])
            ->assertSessionHasErrors(['title', 'content']);
    });

    it('atualiza conteúdo com sucesso e redireciona', function () {
        Config::set('tes_constants.admins', ['admin@test.com']);

        $admin = createAdminUser();
        $admin->update(['email' => 'admin@test.com']);

        DB::table('editable_contents')->insert([
            'slug' => 'precedentes-vinculantes-cpc',
            'title' => 'Título Original',
            'content' => '<p>Conteúdo original</p>',
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put('/admin/content/precedentes-vinculantes-cpc', [
                'title' => 'Título Atualizado',
                'content' => '<p>Novo conteúdo</p>',
                'published' => true,
            ])
            ->assertRedirect(route('content.show', 'precedentes-vinculantes-cpc'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('editable_contents', [
            'slug' => 'precedentes-vinculantes-cpc',
            'title' => 'Título Atualizado',
        ]);
    });

    it('redireciona para homepage ao atualizar conteúdo precedentes-home', function () {
        Config::set('tes_constants.admins', ['admin@test.com']);

        $admin = createAdminUser();
        $admin->update(['email' => 'admin@test.com']);

        DB::table('editable_contents')->insert([
            'slug' => 'precedentes-home',
            'title' => 'Conteúdo Home',
            'content' => '<p>Texto da home</p>',
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put('/admin/content/precedentes-home', [
                'title' => 'Conteúdo Home Atualizado',
                'content' => '<p>Novo texto da home</p>',
            ])
            ->assertRedirect(route('searchpage'));
    });

});
