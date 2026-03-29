<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('registered', 'web');

    Config::set('subscription.tier_product_ids', ['prod_pro', 'prod_premium']);
    Config::set('subscription.tier_labels', ['prod_pro' => 'PRO', 'prod_premium' => 'PREMIUM']);
});

// ==========================================
// Acesso — pública vs privada
// ==========================================

describe('Acesso à página pública', function () {

    it('guest acessa coleção pública com sucesso', function () {
        $owner = User::factory()->create(['name' => 'pub-owner']);
        $collection = Collection::factory()->public()->for($owner)->create(['title' => 'Minha Coleção Pública']);

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSuccessful()
            ->assertSee('Minha Coleção Pública');
    });

    it('usuário logado acessa coleção pública com sucesso', function () {
        $owner = User::factory()->create(['name' => 'pub-owner2']);
        $collection = Collection::factory()->public()->for($owner)->create(['title' => 'Coleção Pública']);
        $visitor = User::factory()->create();

        $this->actingAs($visitor)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSuccessful()
            ->assertSee('Coleção Pública');
    });

    it('guest recebe 403 para coleção privada', function () {
        $owner = User::factory()->create(['name' => 'priv-owner']);
        $collection = Collection::factory()->private()->for($owner)->create();

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertForbidden();
    });

    it('outro usuário recebe 403 para coleção privada', function () {
        $owner = User::factory()->create(['name' => 'priv-owner2']);
        $collection = Collection::factory()->private()->for($owner)->create();
        $visitor = User::factory()->create();

        $this->actingAs($visitor)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertForbidden();
    });

    it('dono acessa sua própria coleção privada', function () {
        $owner = User::factory()->create(['name' => 'priv-dono']);
        $collection = Collection::factory()->private()->for($owner)->create(['title' => 'Privada do Dono']);

        $this->actingAs($owner)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSuccessful()
            ->assertSee('Privada do Dono');
    });

    it('retorna 404 para usuário inexistente', function () {
        $this->get('/colecoes/usuario-inexistente-xyz/qualquer-slug')
            ->assertNotFound();
    });

    it('retorna 404 para slug inexistente', function () {
        $owner = User::factory()->create(['name' => 'slug-test-owner']);

        $this->get('/colecoes/slug-test-owner/slug-que-nao-existe')
            ->assertNotFound();
    });

});

// ==========================================
// Conteúdo da view
// ==========================================

describe('Conteúdo da página pública', function () {

    it('exibe o título e descrição da coleção', function () {
        $owner = User::factory()->create(['name' => 'content-owner']);
        $collection = Collection::factory()->public()->for($owner)->create([
            'title' => 'Teses Trabalhistas',
            'description' => 'Seleção de teses do TST sobre vínculo empregatício.',
        ]);

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('Teses Trabalhistas')
            ->assertSee('Seleção de teses do TST sobre vínculo empregatício.');
    });

    it('exibe o nome do dono na página', function () {
        $owner = User::factory()->create(['name' => 'dono-visivel']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('dono-visivel');
    });

    it('exibe botões de compartilhamento', function () {
        $owner = User::factory()->create(['name' => 'share-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('WhatsApp')
            ->assertSee('Copiar link');
    });

    it('exibe estado vazio quando não há itens', function () {
        $owner = User::factory()->create(['name' => 'empty-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('Esta coleção ainda não tem itens.');
    });

    it('não exibe estado vazio quando há itens', function () {
        $owner = User::factory()->create(['name' => 'items-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();
        CollectionItem::factory()->tese()->for($collection)->create();

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertDontSee('Esta coleção ainda não tem itens.');
    });

    it('exibe badges de tribunal e tipo para cada item', function () {
        $owner = User::factory()->create(['name' => 'badge-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();
        CollectionItem::factory()->tese()->for($collection)->create(['tribunal' => 'stf']);

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('Tese')
            ->assertSee('STF');
    });

    it('exibe badge de privada quando coleção é privada e dono acessa', function () {
        $owner = User::factory()->create(['name' => 'priv-badge-owner']);
        $collection = Collection::factory()->private()->for($owner)->create();

        $this->actingAs($owner)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('Privada');
    });

});

// ==========================================
// Botão editar — dono logado
// ==========================================

describe('Botão editar para o dono', function () {

    it('dono vê botão de editar coleção', function () {
        $owner = User::factory()->create(['name' => 'edit-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->actingAs($owner)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('Editar coleção');
    });

    it('botão editar aponta para a rota correta', function () {
        $owner = User::factory()->create(['name' => 'edit-link-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->actingAs($owner)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee(route('colecoes.edit', $collection->id));
    });

    it('visitante logado não vê botão editar', function () {
        $owner = User::factory()->create(['name' => 'no-edit-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();
        $visitor = User::factory()->create();

        $this->actingAs($visitor)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertDontSee('Editar coleção');
    });

    it('guest não vê botão editar', function () {
        $owner = User::factory()->create(['name' => 'no-edit-guest-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertDontSee('Editar coleção');
    });

});

// ==========================================
// CTA para guests
// ==========================================

describe('CTA de registro para guests', function () {

    it('guest vê CTA de registro', function () {
        $owner = User::factory()->create(['name' => 'cta-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertSee('Organize suas pesquisas')
            ->assertSee('Criar conta grátis');
    });

    it('usuário logado não vê CTA de registro', function () {
        $owner = User::factory()->create(['name' => 'no-cta-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();
        $visitor = User::factory()->create();

        $this->actingAs($visitor)
            ->get(route('colecoes.show', [$owner->name, $collection->slug]))
            ->assertDontSee('Organize suas pesquisas');
    });

});

// ==========================================
// CollectionItem::resolveLabel (unit)
// ==========================================

describe('CollectionItem::resolveLabel', function () {

    it('retorna label de tese com número e tema', function () {
        $content = (object) ['numero' => 42, 'tema_texto' => 'Responsabilidade civil do Estado'];

        expect(CollectionItem::resolveLabel('tese', $content))
            ->toBe('Tema 42 — Responsabilidade civil do Estado');
    });

    it('retorna label de tese só com número quando sem tema', function () {
        $content = (object) ['numero' => 10];

        expect(CollectionItem::resolveLabel('tese', $content))
            ->toBe('Tema 10');
    });

    it('retorna label de súmula com número e título', function () {
        $content = (object) ['numero' => 7, 'titulo' => 'Impossibilidade de citação por edital'];

        expect(CollectionItem::resolveLabel('sumula', $content))
            ->toBe('Súmula nº 7 — Impossibilidade de citação por edital');
    });

    it('retorna mensagem padrão para conteúdo nulo', function () {
        expect(CollectionItem::resolveLabel('tese', null))
            ->toBe('Conteúdo não disponível');
    });

});

// ==========================================
// CollectionItem::resolveDetailUrl (unit)
// ==========================================

describe('CollectionItem::resolveDetailUrl', function () {

    it('retorna URL correta para tese do STF', function () {
        $content = (object) ['numero' => 100];

        expect(CollectionItem::resolveDetailUrl('tese', 'stf', $content))
            ->toBe(route('stftesepage', ['tese' => 100]));
    });

    it('retorna URL correta para tese do TST', function () {
        $content = (object) ['numero' => 5];

        expect(CollectionItem::resolveDetailUrl('tese', 'tst', $content))
            ->toBe(route('tsttesepage', ['tese' => 5]));
    });

    it('retorna URL correta para súmula do STJ', function () {
        $content = (object) ['numero' => 7];

        expect(CollectionItem::resolveDetailUrl('sumula', 'stj', $content))
            ->toBe(route('stjsumulapage', ['sumula' => 7]));
    });

    it('retorna null para tribunal sem rota (ex: carf)', function () {
        $content = (object) ['numero' => 1];

        expect(CollectionItem::resolveDetailUrl('sumula', 'carf', $content))
            ->toBeNull();
    });

    it('retorna null para conteúdo nulo', function () {
        expect(CollectionItem::resolveDetailUrl('tese', 'stf', null))
            ->toBeNull();
    });

    it('retorna null para conteúdo sem número', function () {
        $content = (object) ['titulo' => 'Sem número'];

        expect(CollectionItem::resolveDetailUrl('sumula', 'stj', $content))
            ->toBeNull();
    });

});

// ==========================================
// Diretório público /colecoes
// ==========================================

describe('Diretório público de coleções', function () {

    it('retorna 200 para qualquer visitante', function () {
        $this->get(route('colecoes.directory'))
            ->assertSuccessful();
    });

    it('lista coleções públicas', function () {
        $owner = User::factory()->create(['name' => 'dir-owner']);
        Collection::factory()->public()->for($owner)->create(['title' => 'Coleção Visível no Diretório']);

        $this->get(route('colecoes.directory'))
            ->assertSee('Coleção Visível no Diretório');
    });

    it('não lista coleções privadas', function () {
        $owner = User::factory()->create(['name' => 'priv-dir-owner']);
        Collection::factory()->private()->for($owner)->create(['title' => 'Coleção Privada Oculta']);

        $this->get(route('colecoes.directory'))
            ->assertDontSee('Coleção Privada Oculta');
    });

    it('exibe o nome do dono na listagem', function () {
        $owner = User::factory()->create(['name' => 'dono-listagem']);
        Collection::factory()->public()->for($owner)->create();

        $this->get(route('colecoes.directory'))
            ->assertSee('dono-listagem');
    });

    it('exibe link para a página da coleção', function () {
        $owner = User::factory()->create(['name' => 'link-dir-owner']);
        $collection = Collection::factory()->public()->for($owner)->create();

        $this->get(route('colecoes.directory'))
            ->assertSee(route('colecoes.show', [$owner->name, $collection->slug]));
    });

    it('guest vê CTA de registro', function () {
        $this->get(route('colecoes.directory'))
            ->assertSee('Criar conta grátis');
    });

    it('usuário logado não vê CTA de registro', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('colecoes.directory'))
            ->assertDontSee('Criar conta grátis');
    });

    it('exibe estado vazio quando não há coleções públicas', function () {
        $this->get(route('colecoes.directory'))
            ->assertSee('Nenhuma coleção pública ainda.');
    });

});
