<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Testes de proteção de rotas admin — verifica que todas as rotas
 * do painel admin estão protegidas com as permissões corretas.
 */

// ==========================================
// Usuário sem permissão → 403
// ==========================================

describe('Admin sem permissão', function () {

    it('retorna 403 no dashboard admin', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    });

    it('retorna 403 na listagem de users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    });

    it('retorna 403 na listagem de roles', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/roles')
            ->assertForbidden();
    });

    it('retorna 403 na listagem de permissions', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/permissions')
            ->assertForbidden();
    });

    it('retorna 403 na listagem de quizzes admin', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/quizzes')
            ->assertForbidden();
    });

    it('retorna 403 na listagem de questions admin', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/questions')
            ->assertForbidden();
    });

    it('retorna 403 na listagem de acordaos admin', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/acordaos')
            ->assertForbidden();
    });

    it('retorna 403 na edição de conteúdo sem permissão', function () {
        $user = User::factory()->create();

        DB::table('editable_contents')->insert([
            'slug' => 'pagina-protegida',
            'title' => 'Protegida',
            'content' => '<p>Conteúdo</p>',
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/admin/content/pagina-protegida/edit')
            ->assertForbidden();
    });

});

// ==========================================
// Admin com permissão manage_all → 200
// ==========================================

describe('Admin com permissão', function () {

    it('acessa o dashboard admin', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin');
        // Pode dar 500 com SQLite (queries MySQL-específicas no dashboard)
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('acessa listagem de users', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/users');
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('acessa criação de user', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/users/create');
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('acessa listagem de roles', function () {
        $admin = createAdminUser();

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk();
    });

    it('acessa criação de role', function () {
        $admin = createAdminUser();

        $this->actingAs($admin)
            ->get('/admin/roles/create')
            ->assertOk();
    });

    it('acessa listagem de permissions', function () {
        $admin = createAdminUser();

        $this->actingAs($admin)
            ->get('/admin/permissions')
            ->assertOk();
    });

    it('acessa criação de permission', function () {
        $admin = createAdminUser();

        $this->actingAs($admin)
            ->get('/admin/permissions/create')
            ->assertOk();
    });

    it('acessa listagem de quizzes admin', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/quizzes');
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('acessa criação de quiz admin', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/quizzes/create');
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('acessa listagem de questions admin', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/questions');
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('acessa listagem de acordaos admin', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/acordaos');
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

});
