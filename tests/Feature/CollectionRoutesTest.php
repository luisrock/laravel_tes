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
// Proteção de rotas — guest redireciona
// ==========================================

describe('Proteção de rotas — guest', function () {

    it('redireciona guest em GET /minha-conta/colecoes', function () {
        $this->get(route('colecoes.index'))->assertRedirect(route('login'));
    });

    it('redireciona guest em POST /minha-conta/colecoes', function () {
        $this->post(route('colecoes.store'))->assertRedirect(route('login'));
    });

    it('redireciona guest em PUT /minha-conta/colecoes/{id}', function () {
        $this->put(route('colecoes.update', 1))->assertRedirect(route('login'));
    });

    it('redireciona guest em DELETE /minha-conta/colecoes/{id}', function () {
        $this->delete(route('colecoes.destroy', 1))->assertRedirect(route('login'));
    });

    it('redireciona guest em GET /api/colecoes/modal', function () {
        $this->get(route('colecoes.modal', ['type' => 'tese', 'tribunal' => 'stf', 'contentId' => 1]))
            ->assertRedirect(route('login'));
    });

});

// ==========================================
// Rota pública — coleção pública e privada
// ==========================================

describe('Rota pública de coleção', function () {

    it('retorna 404 para username inexistente', function () {
        $this->get('/colecoes/usuario-inexistente/qualquer-slug')->assertNotFound();
    });

    it('retorna 404 para slug inexistente', function () {
        $user = User::factory()->create(['name' => 'joao-teste']);

        $this->get('/colecoes/joao-teste/slug-inexistente')->assertNotFound();
    });

    it('retorna 403 para coleção privada acessada por guest', function () {
        $owner = User::factory()->create(['name' => 'dono-colecao']);
        $collection = Collection::factory()->private()->create(['user_id' => $owner->id]);

        $this->get(route('colecoes.show', ['username' => 'dono-colecao', 'slug' => $collection->slug]))
            ->assertForbidden();
    });

    it('retorna 403 para coleção privada acessada por outro usuário', function () {
        $owner = User::factory()->create(['name' => 'dono-colecao2']);
        $collection = Collection::factory()->private()->create(['user_id' => $owner->id]);

        $outro = User::factory()->create();

        $this->actingAs($outro)
            ->get(route('colecoes.show', ['username' => 'dono-colecao2', 'slug' => $collection->slug]))
            ->assertForbidden();
    });

    it('dono acessa sua própria coleção privada', function () {
        $owner = User::factory()->create(['name' => 'dono-priv3']);
        $collection = Collection::factory()->private()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->get(route('colecoes.show', ['username' => 'dono-priv3', 'slug' => $collection->slug]))
            ->assertSuccessful();
    });

    it('guest acessa coleção pública', function () {
        $owner = User::factory()->create(['name' => 'dono-pub']);
        $collection = Collection::factory()->public()->create(['user_id' => $owner->id]);

        $this->get(route('colecoes.show', ['username' => 'dono-pub', 'slug' => $collection->slug]))
            ->assertSuccessful();
    });

});

// ==========================================
// CollectionController — CRUD
// ==========================================

describe('CollectionController — index', function () {

    it('usuário autenticado acessa /minha-conta/colecoes', function () {
        $user = User::factory()->create();
        Collection::factory()->count(2)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('colecoes.index'))
            ->assertSuccessful();
    });

});

describe('CollectionController — store', function () {

    it('cria coleção com dados válidos', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('colecoes.store'), [
                'title' => 'Minha Nova Coleção',
                'description' => 'Descrição opcional',
            ])
            ->assertRedirect();

        expect(Collection::where('user_id', $user->id)->where('title', 'Minha Nova Coleção')->exists())->toBeTrue();
    });

    it('falha ao criar coleção sem título', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('colecoes.store'), ['description' => 'Sem título'])
            ->assertSessionHasErrors('title');
    });

    it('falha ao criar coleção com título menor que 2 caracteres', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('colecoes.store'), ['title' => 'A'])
            ->assertSessionHasErrors('title');
    });

    it('falha ao criar coleção com título maior que 100 caracteres', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('colecoes.store'), ['title' => str_repeat('a', 101)])
            ->assertSessionHasErrors('title');
    });

    it('falha ao criar coleção com descrição maior que 500 caracteres', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('colecoes.store'), [
                'title' => 'Título válido',
                'description' => str_repeat('x', 501),
            ])
            ->assertSessionHasErrors('description');
    });

    it('retorna 403 quando usuário atingiu o limite de coleções', function () {
        $user = User::factory()->create();
        // Limite de registrado é 3; criar 3 coleções para esgotar
        Collection::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('colecoes.store'), ['title' => 'Quarta coleção'])
            ->assertForbidden();
    });

    it('registrado não consegue criar coleção privada (is_private é ignorado)', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('colecoes.store'), [
                'title' => 'Tentativa Privada',
                'is_private' => true,
            ]);

        $collection = Collection::where('user_id', $user->id)->first();
        expect($collection->is_private)->toBeFalse();
    });

});

describe('CollectionController — update', function () {

    it('dono atualiza a própria coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('colecoes.update', $collection->id), [
                'title' => 'Título Atualizado',
                'description' => null,
            ])
            ->assertRedirect();

        expect($collection->fresh()->title)->toBe('Título Atualizado');
    });

    it('retorna 404 ao tentar atualizar coleção de outro usuário', function () {
        $owner = User::factory()->create();
        $outro = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($outro)
            ->put(route('colecoes.update', $collection->id), ['title' => 'Invasão'])
            ->assertNotFound();
    });

    it('falha ao atualizar com título vazio', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('colecoes.update', $collection->id), ['title' => ''])
            ->assertSessionHasErrors('title');
    });

});

describe('CollectionController — destroy', function () {

    it('dono exclui a própria coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('colecoes.destroy', $collection->id))
            ->assertRedirect(route('colecoes.index'));

        expect(Collection::find($collection->id))->toBeNull();
    });

    it('retorna 404 ao tentar excluir coleção de outro usuário', function () {
        $owner = User::factory()->create();
        $outro = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($outro)
            ->delete(route('colecoes.destroy', $collection->id))
            ->assertNotFound();
    });

});

// ==========================================
// CollectionController — itens
// ==========================================

describe('CollectionController — storeItem', function () {

    it('adiciona item válido à coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('colecoes.itens.store', $collection->id), [
                'content_type' => 'tese',
                'content_id' => 42,
                'tribunal' => 'stf',
            ])
            ->assertRedirect();

        expect(CollectionItem::where('collection_id', $collection->id)->where('content_id', 42)->exists())->toBeTrue();
    });

    it('não duplica item já existente na coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $payload = ['content_type' => 'tese', 'content_id' => 99, 'tribunal' => 'stj'];

        $this->actingAs($user)->post(route('colecoes.itens.store', $collection->id), $payload);
        $this->actingAs($user)->post(route('colecoes.itens.store', $collection->id), $payload);

        expect(CollectionItem::where('collection_id', $collection->id)->where('content_id', 99)->count())->toBe(1);
    });

    it('falha ao adicionar item com content_type inválido', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('colecoes.itens.store', $collection->id), [
                'content_type' => 'quiz',
                'content_id' => 1,
                'tribunal' => 'stf',
            ])
            ->assertSessionHasErrors('content_type');
    });

    it('retorna 404 ao adicionar item em coleção de outro usuário', function () {
        $owner = User::factory()->create();
        $outro = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($outro)
            ->post(route('colecoes.itens.store', $collection->id), [
                'content_type' => 'tese',
                'content_id' => 1,
                'tribunal' => 'stf',
            ])
            ->assertNotFound();
    });

});

describe('CollectionController — destroyItem', function () {

    it('dono remove item da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);
        $item = CollectionItem::factory()->tese()->create(['collection_id' => $collection->id]);

        $this->actingAs($user)
            ->delete(route('colecoes.itens.destroy', [$collection->id, $item->id]))
            ->assertRedirect();

        expect(CollectionItem::find($item->id))->toBeNull();
    });

    it('retorna 404 ao remover item de coleção de outro usuário', function () {
        $owner = User::factory()->create();
        $outro = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $owner->id]);
        $item = CollectionItem::factory()->tese()->create(['collection_id' => $collection->id]);

        $this->actingAs($outro)
            ->delete(route('colecoes.itens.destroy', [$collection->id, $item->id]))
            ->assertNotFound();
    });

});

describe('CollectionController — reorderItems', function () {

    it('reordena itens da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);
        $item1 = CollectionItem::factory()->tese()->create(['collection_id' => $collection->id, 'order' => 0]);
        $item2 = CollectionItem::factory()->sumula()->create(['collection_id' => $collection->id, 'order' => 1]);

        $this->actingAs($user)
            ->patch(route('colecoes.itens.reorder', $collection->id), [
                'order' => [$item2->id, $item1->id],
            ])
            ->assertRedirect();

        expect($item1->fresh()->order)->toBe(1)
            ->and($item2->fresh()->order)->toBe(0);
    });

    it('falha ao reordenar sem o campo order', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('colecoes.itens.reorder', $collection->id), [])
            ->assertSessionHasErrors('order');
    });

});

// ==========================================
// CollectionModalController — API interna
// ==========================================

describe('CollectionModalController — modal', function () {

    it('retorna JSON com coleções do usuário e flag has_item', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);
        CollectionItem::factory()->tese()->create([
            'collection_id' => $collection->id,
            'content_id' => 10,
            'tribunal' => 'stf',
        ]);

        $this->actingAs($user)
            ->get(route('colecoes.modal', ['type' => 'tese', 'tribunal' => 'stf', 'contentId' => 10]))
            ->assertSuccessful()
            ->assertJsonStructure(['collections', 'can_create', 'can_be_private'])
            ->assertJsonFragment(['has_item' => true]);
    });

    it('retorna has_item false quando item não está na coleção', function () {
        $user = User::factory()->create();
        Collection::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('colecoes.modal', ['type' => 'tese', 'tribunal' => 'stf', 'contentId' => 999]))
            ->assertSuccessful()
            ->assertJsonFragment(['has_item' => false]);
    });

});
