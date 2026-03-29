<?php

use App\Livewire\SaveToCollectionModal;
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
// Renderização e abertura
// ==========================================

describe('SaveToCollectionModal — renderização', function () {

    it('renderiza sem exibir o modal inicialmente', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->assertSet('isOpen', false);
    });

    it('abre o modal com os dados do item', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 42)
            ->assertSet('isOpen', true)
            ->assertSet('contentType', 'tese')
            ->assertSet('tribunal', 'stf')
            ->assertSet('contentId', 42);
    });

    it('fecha o modal', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->call('close')
            ->assertSet('isOpen', false);
    });

    it('exibe as coleções do usuário ao abrir', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->create(['title' => 'Minha Coleção']);

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->assertSee('Minha Coleção');
    });

    it('não exibe coleções de outro usuário', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Collection::factory()->for($other)->create(['title' => 'Coleção Alheia']);

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->assertDontSee('Coleção Alheia');
    });

    it('marca como has_item a coleção que já contém o item', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->tese()->create([
            'tribunal' => 'stf',
            'content_id' => 42,
        ]);

        $component = Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 42);

        $collections = $component->get('collections');
        expect($collections[0]['has_item'])->toBeTrue();
    });

    it('exibe estado vazio quando usuário não tem coleções', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->assertSee('ainda não tem coleções');
    });

    it('marca is_full quando coleção está no limite de itens', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->tese()
            ->count(20)->sequence(fn ($s) => ['content_id' => $s->index + 1])->create();

        $component = Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 9999);

        $collections = $component->get('collections');
        expect($collections[0]['is_full'])->toBeTrue();
    });

    it('não exibe texto "cheio" na lista de coleções', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->tese()
            ->count(20)->sequence(fn ($s) => ['content_id' => $s->index + 1])->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 9999)
            ->assertDontSee('cheio');
    });

    it('exibe CTA de limite de itens no footer quando há coleção cheia', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->tese()
            ->count(20)->sequence(fn ($s) => ['content_id' => $s->index + 1])->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 9999)
            ->assertSee('Limite de itens atingido');
    });

});

// ==========================================
// Toggle (adicionar / remover)
// ==========================================

describe('SaveToCollectionModal — toggle', function () {

    it('adiciona item à coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'sumula', 'stj', 10)
            ->call('toggle', $collection->id);

        expect(CollectionItem::where([
            'collection_id' => $collection->id,
            'content_type' => 'sumula',
            'content_id' => 10,
            'tribunal' => 'stj',
        ])->exists())->toBeTrue();
    });

    it('remove item já existente da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->tese()->create([
            'tribunal' => 'stf',
            'content_id' => 5,
        ]);

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 5)
            ->call('toggle', $collection->id);

        expect(CollectionItem::where([
            'collection_id' => $collection->id,
            'content_type' => 'tese',
            'content_id' => 5,
        ])->exists())->toBeFalse();
    });

    it('atualiza has_item após toggle', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 7);

        $before = $component->get('collections');
        expect($before[0]['has_item'])->toBeFalse();

        $component->call('toggle', $collection->id);

        $after = $component->get('collections');
        expect($after[0]['has_item'])->toBeTrue();
    });

    it('retorna 403 ao tentar toglar em coleção de outro usuário', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($other)->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->call('toggle', $collection->id)
            ->assertForbidden();
    });

});

// ==========================================
// Criar coleção inline e adicionar item
// ==========================================

describe('SaveToCollectionModal — createAndAdd', function () {

    it('cria nova coleção e adiciona o item', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stj', 99)
            ->set('newTitle', 'Nova Coleção Inline')
            ->call('createAndAdd');

        $collection = Collection::where('user_id', $user->id)->where('title', 'Nova Coleção Inline')->first();
        expect($collection)->not->toBeNull();
        expect(CollectionItem::where([
            'collection_id' => $collection->id,
            'content_type' => 'tese',
            'content_id' => 99,
            'tribunal' => 'stj',
        ])->exists())->toBeTrue();
    });

    it('fecha o formulário de criação após criar', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stj', 1)
            ->set('showCreate', true)
            ->set('newTitle', 'Coleção Nova')
            ->call('createAndAdd')
            ->assertSet('showCreate', false)
            ->assertSet('newTitle', '');
    });

    it('valida título obrigatório no createAndAdd', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->set('newTitle', '')
            ->call('createAndAdd')
            ->assertHasErrors(['newTitle' => 'required']);
    });

    it('exibe CTA de upgrade no lugar de Nova Coleção quando limite atingido', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->assertSet('canCreate', false)
            ->assertSee('Limite de coleções atingido');
    });

    it('não permite criar coleção além do limite', function () {
        Config::set('subscription.tier_labels', []);
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->set('newTitle', 'Excede Limite')
            ->call('createAndAdd')
            ->assertForbidden();
    });

});

// ==========================================
// justToggledId e evento item-toggled
// ==========================================

describe('SaveToCollectionModal — justToggledId e auto-close', function () {

    it('toggle define justToggledId com o id da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->call('toggle', $collection->id)
            ->assertSet('justToggledId', $collection->id);
    });

    it('toggle despacha evento item-toggled', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->call('toggle', $collection->id)
            ->assertDispatched('item-toggled');
    });

    it('open reseta justToggledId para 0', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test(SaveToCollectionModal::class)
            ->call('open', 'tese', 'stf', 1)
            ->call('toggle', $collection->id);

        $component->assertSet('justToggledId', $collection->id);

        $component->call('open', 'sumula', 'stj', 5)
            ->assertSet('justToggledId', 0);
    });

});
