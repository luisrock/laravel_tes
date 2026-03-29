<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Database\QueryException;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('registered', 'web');
});

// ==========================================
// Collection — criação e slug
// ==========================================

describe('Collection — criação e slug', function () {

    it('cria uma coleção com campos corretos', function () {
        $user = User::factory()->create();

        $collection = Collection::create([
            'user_id' => $user->id,
            'title' => 'Minhas Teses STF',
            'description' => 'Coleção para revisar',
            'is_private' => false,
        ]);

        expect($collection)
            ->title->toBe('Minhas Teses STF')
            ->description->toBe('Coleção para revisar')
            ->is_private->toBeFalse()
            ->slug->toBe('minhas-teses-stf')
            ->user_id->toBe($user->id);
    });

    it('gera slug automaticamente a partir do título', function () {
        $user = User::factory()->create();

        $collection = Collection::create([
            'user_id' => $user->id,
            'title' => 'Direito Civil — OAB 2025',
        ]);

        expect($collection->slug)->toBe('direito-civil-oab-2025');
    });

    it('gera slug único quando já existe o mesmo para o usuário', function () {
        $user = User::factory()->create();

        $first = Collection::create(['user_id' => $user->id, 'title' => 'Minha Lista']);
        $second = Collection::create(['user_id' => $user->id, 'title' => 'Minha Lista']);
        $third = Collection::create(['user_id' => $user->id, 'title' => 'Minha Lista']);

        expect($first->slug)->toBe('minha-lista');
        expect($second->slug)->toBe('minha-lista-2');
        expect($third->slug)->toBe('minha-lista-3');
    });

    it('permite o mesmo slug para usuários diferentes', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $collectionA = Collection::create(['user_id' => $userA->id, 'title' => 'Minha Lista']);
        $collectionB = Collection::create(['user_id' => $userB->id, 'title' => 'Minha Lista']);

        expect($collectionA->slug)->toBe('minha-lista');
        expect($collectionB->slug)->toBe('minha-lista');
    });

    it('respeita o limite de 100 chars no título via validação de banco', function () {
        $user = User::factory()->create();

        $collection = Collection::factory()->for($user)->create([
            'title' => str_repeat('a', 100),
        ]);

        expect(strlen($collection->title))->toBe(100);
    });

    it('is_private é falso por padrão', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        expect($collection->is_private)->toBeFalse();
    });

    it('factory private() define is_private como true', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->private()->create();

        expect($collection->is_private)->toBeTrue();
    });

});

// ==========================================
// Collection — relacionamentos
// ==========================================

describe('Collection — relacionamentos', function () {

    it('pertence a um usuário', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        expect($collection->user)->toBeInstanceOf(User::class);
        expect($collection->user->id)->toBe($user->id);
    });

    it('usuário possui suas coleções via hasMany', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        expect($user->collections)->toHaveCount(3);
    });

    it('deletar usuário deleta suas coleções em cascata', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(2)->create();

        $user->delete();

        expect(Collection::count())->toBe(0);
    });

});

// ==========================================
// Collection — hasItem
// ==========================================

describe('Collection — hasItem', function () {

    it('retorna true quando item está na coleção', function () {
        $collection = Collection::factory()->create();

        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese',
            'content_id' => 42,
            'tribunal' => 'stf',
        ]);

        expect($collection->hasItem('tese', 42, 'stf'))->toBeTrue();
    });

    it('retorna false quando item não está na coleção', function () {
        $collection = Collection::factory()->create();

        expect($collection->hasItem('tese', 99, 'stf'))->toBeFalse();
    });

});

// ==========================================
// CollectionItem — unique constraint
// ==========================================

describe('CollectionItem — unique constraint', function () {

    it('impede duplicata do mesmo item na mesma coleção', function () {
        $collection = Collection::factory()->create();

        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese',
            'content_id' => 1,
            'tribunal' => 'stf',
        ]);

        expect(fn () => CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese',
            'content_id' => 1,
            'tribunal' => 'stf',
        ]))->toThrow(QueryException::class);
    });

    it('permite o mesmo item em coleções diferentes', function () {
        $collectionA = Collection::factory()->create();
        $collectionB = Collection::factory()->create();

        CollectionItem::factory()->for($collectionA)->create([
            'content_type' => 'tese',
            'content_id' => 1,
            'tribunal' => 'stf',
        ]);

        CollectionItem::factory()->for($collectionB)->create([
            'content_type' => 'tese',
            'content_id' => 1,
            'tribunal' => 'stf',
        ]);

        expect(CollectionItem::count())->toBe(2);
    });

    it('permite a mesma tese e súmula com mesmo id na mesma coleção (tipos diferentes)', function () {
        $collection = Collection::factory()->create();

        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese',
            'content_id' => 1,
            'tribunal' => 'stf',
        ]);

        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'sumula',
            'content_id' => 1,
            'tribunal' => 'stf',
        ]);

        expect(CollectionItem::count())->toBe(2);
    });

    it('deletar coleção deleta seus itens em cascata', function () {
        $collection = Collection::factory()->create();
        CollectionItem::factory()->for($collection)->sequence(
            ['content_type' => 'tese', 'content_id' => 1, 'tribunal' => 'stf'],
            ['content_type' => 'sumula', 'content_id' => 2, 'tribunal' => 'stj'],
            ['content_type' => 'tese', 'content_id' => 3, 'tribunal' => 'tst'],
        )->count(3)->create();

        $collection->delete();

        expect(CollectionItem::count())->toBe(0);
    });

});

// ==========================================
// CollectionItem — ordenação
// ==========================================

describe('CollectionItem — ordenação', function () {

    it('retorna itens ordenados pelo campo order', function () {
        $collection = Collection::factory()->create();

        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese', 'content_id' => 1, 'tribunal' => 'stf', 'order' => 2,
        ]);
        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese', 'content_id' => 2, 'tribunal' => 'stf', 'order' => 0,
        ]);
        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese', 'content_id' => 3, 'tribunal' => 'stf', 'order' => 1,
        ]);

        $orders = $collection->items->pluck('order')->toArray();

        expect($orders)->toBe([0, 1, 2]);
    });

});

// ==========================================
// Collection — scope public
// ==========================================

describe('Collection — scope public', function () {

    it('retorna apenas coleções públicas', function () {
        Collection::factory()->count(2)->create(['is_private' => false]);
        Collection::factory()->count(3)->private()->create();

        expect(Collection::public()->count())->toBe(2);
    });

});

// ==========================================
// CollectionItem — getContent
// ==========================================

describe('CollectionItem — getContent', function () {

    it('retorna null para content_type inválido', function () {
        $item = CollectionItem::factory()->create(['content_type' => 'invalido']);

        expect($item->getContent())->toBeNull();
    });

    it('retorna null quando o registro não existe na tabela', function () {
        \Schema::create('stf_teses', fn ($t) => $t->id());
        $item = CollectionItem::factory()->tese()->create(['tribunal' => 'stf', 'content_id' => 999]);

        expect($item->getContent())->toBeNull();

        \Schema::drop('stf_teses');
    });

    it('retorna object (não Eloquent Model) quando o registro existe', function () {
        \Schema::create('stf_teses', fn ($t) => $t->id());
        \DB::table('stf_teses')->insert(['id' => 1]);
        $item = CollectionItem::factory()->tese()->create(['tribunal' => 'stf', 'content_id' => 1]);

        $content = $item->getContent();

        expect($content)->toBeObject();
        expect($content)->not->toBeInstanceOf(\Illuminate\Database\Eloquent\Model::class);

        \Schema::drop('stf_teses');
    });

});
