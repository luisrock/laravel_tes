<?php

use App\Livewire\CollectionEdit;
use App\Livewire\CollectionList;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('registered', 'web');
    \Spatie\Permission\Models\Role::findOrCreate('admin', 'web');

    Config::set('subscription.tier_product_ids', ['prod_pro', 'prod_premium']);
    Config::set('subscription.tier_labels', ['prod_pro' => 'PRO', 'prod_premium' => 'PREMIUM']);
});

// ==========================================
// CollectionList — renderização
// ==========================================

describe('CollectionList — renderização', function () {

    it('renderiza o componente para usuário autenticado', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSuccessful();
    });

    it('exibe estado vazio quando não há coleções', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSee('Você ainda não tem coleções');
    });

    it('exibe as coleções existentes do usuário', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->create(['title' => 'Minha Coleção de Teses']);

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSee('Minha Coleção de Teses');
    });

    it('não exibe coleções de outro usuário', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Collection::factory()->for($other)->create(['title' => 'Coleção Alheia']);

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertDontSee('Coleção Alheia');
    });

    it('exibe badge Privada para coleção privada', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->private()->create(['title' => 'Reservada']);

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSee('Privada');
    });

    it('exibe badge Pública para coleção pública', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->public()->create(['title' => 'Aberta']);

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSee('Pública');
    });

    it('exibe contador de coleções do plano registrado', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(2)->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSee('2 de 3');
    });

});

// ==========================================
// CollectionList — formulário de criação
// ==========================================

describe('CollectionList — criar coleção', function () {

    it('exibe o formulário ao chamar openCreateForm', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->assertSet('showCreateForm', true)
            ->assertSee('Nova coleção');
    });

    it('fecha o formulário ao cancelar', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->call('cancelCreate')
            ->assertSet('showCreateForm', false);
    });

    it('valida título obrigatório', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->set('newTitle', '')
            ->call('createCollection')
            ->assertHasErrors(['newTitle' => 'required']);
    });

    it('valida comprimento mínimo do título', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->set('newTitle', 'A')
            ->call('createCollection')
            ->assertHasErrors(['newTitle' => 'min']);
    });

    it('cria coleção com título válido e redireciona para edição', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->set('newTitle', 'Nova Coleção Válida')
            ->call('createCollection')
            ->assertRedirect();

        expect(Collection::where('user_id', $user->id)->where('title', 'Nova Coleção Válida')->exists())->toBeTrue();
    });

    it('gera slug automaticamente ao criar', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->set('newTitle', 'Direito do Trabalho')
            ->call('createCollection');

        $collection = Collection::where('user_id', $user->id)->first();
        expect($collection->slug)->toBe('direito-do-trabalho');
    });

});

// ==========================================
// CollectionList — excluir coleção
// ==========================================

describe('CollectionList — excluir coleção', function () {

    it('exclui a coleção do próprio usuário', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('deleteCollection', $collection->id);

        expect(Collection::find($collection->id))->toBeNull();
    });

    it('retorna 403 ao tentar excluir coleção de outro usuário', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($other)->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('deleteCollection', $collection->id)
            ->assertForbidden();
    });

    it('atualiza canCreate após excluir quando limite estava atingido', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        $collections = Collection::factory()->for($user)->count(3)->create();

        $component = Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSet('canCreate', false);

        $component->call('deleteCollection', $collections->last()->id)
            ->assertSet('canCreate', true);
    });

});

// ==========================================
// CollectionList — CTA de upgrade
// ==========================================

describe('CollectionList — CTA ao atingir limite', function () {

    it('exibe CTA ao clicar em Nova Coleção quando limite é atingido', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->assertSet('showLimitCta', true)
            ->assertSee('Limite de coleções atingido');
    });

    it('não exibe CTA ao renderizar quando ainda há espaço para coleções', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(2)->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertDontSee('Limite de coleções atingido');
    });

    it('define canCreate como false quando limite atingido', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->assertSet('canCreate', false);
    });

    it('fecha o CTA de limite ao cancelar', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        Livewire::actingAs($user)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->assertSet('showLimitCta', true)
            ->call('cancelCreate')
            ->assertSet('showLimitCta', false);
    });

    it('admin pode criar coleções ilimitadas', function () {
        $admin = createAdminUser();
        Collection::factory()->for($admin)->count(10)->create();

        Livewire::actingAs($admin)
            ->test(CollectionList::class)
            ->assertSet('canCreate', true)
            ->assertSet('limits.max_collections', -1);
    });

    it('admin pode criar coleção mesmo além do limite padrão', function () {
        $admin = createAdminUser();
        Collection::factory()->for($admin)->count(3)->create();

        Livewire::actingAs($admin)
            ->test(CollectionList::class)
            ->call('openCreateForm')
            ->assertSet('showCreateForm', true)
            ->assertSet('showLimitCta', false);
    });

});

// ==========================================
// CollectionEdit — renderização
// ==========================================

describe('CollectionEdit — renderização', function () {

    it('carrega os dados da coleção nas propriedades', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create([
            'title' => 'Coleção Editável',
            'description' => 'Descrição de teste',
        ]);

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertSet('title', 'Coleção Editável')
            ->assertSet('description', 'Descrição de teste')
            ->assertSet('collectionId', $collection->id);
    });

    it('lança 404 para coleção de outro usuário', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($other)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertStatus(404);
    });

    it('exibe estado vazio quando não há itens', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertSee('Nenhum item salvo ainda');
    });

    it('exibe badge do tribunal para itens da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->tese()->create([
            'tribunal' => 'stf',
            'content_id' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertSee('STF');
    });

    it('exibe CTA de privacidade para usuário registrado', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertSee('Privacidade exclusiva para assinantes');
    });

    it('exibe toggle de privacidade para usuário PRO', function () {
        $user = createSubscribedUser('prod_pro');
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertSet('limits.can_be_private', true)
            ->assertDontSee('Privacidade exclusiva para assinantes');
    });

    it('admin tem privacidade e limites ilimitados', function () {
        $admin = createAdminUser();
        $collection = Collection::factory()->for($admin)->create();

        Livewire::actingAs($admin)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertSet('limits.can_be_private', true)
            ->assertSet('limits.max_collections', -1)
            ->assertSet('limits.max_items', -1)
            ->assertDontSee('Privacidade exclusiva para assinantes');
    });

    it('passa a coleção para a view', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->assertViewHas('collection');
    });

});

// ==========================================
// CollectionEdit — salvar dados
// ==========================================

describe('CollectionEdit — salvar', function () {

    it('salva o título e descrição atualizados', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create(['title' => 'Título Original']);

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->set('title', 'Título Atualizado')
            ->set('description', 'Nova descrição')
            ->call('save');

        $collection->refresh();
        expect($collection->title)->toBe('Título Atualizado')
            ->and($collection->description)->toBe('Nova descrição');
    });

    it('dispara evento collection-saved ao salvar com sucesso', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->set('title', 'Título Válido')
            ->call('save')
            ->assertDispatched('collection-saved');
    });

    it('valida título obrigatório ao salvar', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->set('title', '')
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    });

    it('não permite tornar coleção privada para usuário registrado', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->public()->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->set('isPrivate', true)
            ->call('save');

        expect($collection->fresh()->is_private)->toBeFalse();
    });

    it('permite tornar coleção privada para usuário PRO', function () {
        $user = createSubscribedUser('prod_pro');
        $collection = Collection::factory()->for($user)->public()->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->set('isPrivate', true)
            ->call('save');

        expect($collection->fresh()->is_private)->toBeTrue();
    });

});

// ==========================================
// CollectionEdit — gerenciar itens
// ==========================================

describe('CollectionEdit — itens', function () {

    it('remove item da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        $item = CollectionItem::factory()->for($collection)->tese()->create(['content_id' => 1]);

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->call('removeItem', $item->id);

        expect(CollectionItem::find($item->id))->toBeNull();
    });

    it('reordena itens da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        $item1 = CollectionItem::factory()->for($collection)->tese()->create(['order' => 0, 'content_id' => 1]);
        $item2 = CollectionItem::factory()->for($collection)->sumula()->create(['order' => 1, 'content_id' => 2]);
        $item3 = CollectionItem::factory()->for($collection)->tese()->create(['order' => 2, 'content_id' => 3]);

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->call('reorderItems', [$item3->id, $item1->id, $item2->id]);

        expect($item3->fresh()->order)->toBe(0)
            ->and($item1->fresh()->order)->toBe(1)
            ->and($item2->fresh()->order)->toBe(2);
    });

    it('despacha evento reorder-saved após reordenar', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        $item1 = CollectionItem::factory()->for($collection)->tese()->create(['order' => 0, 'content_id' => 1]);
        $item2 = CollectionItem::factory()->for($collection)->tese()->create(['order' => 1, 'content_id' => 2]);

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->call('reorderItems', [$item2->id, $item1->id])
            ->assertDispatched('reorder-saved');
    });

});

// ==========================================
// CollectionEdit — excluir coleção
// ==========================================

describe('CollectionEdit — excluir coleção', function () {

    it('exclui a coleção e redireciona para o índice', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->call('deleteCollection')
            ->assertRedirectToRoute('colecoes.index');

        expect(Collection::find($collection->id))->toBeNull();
    });

    it('exclui itens em cascata ao excluir coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->tese()->count(3)->create();

        Livewire::actingAs($user)
            ->test(CollectionEdit::class, ['collectionId' => $collection->id])
            ->call('deleteCollection');

        expect(CollectionItem::where('collection_id', $collection->id)->count())->toBe(0);
    });

});
