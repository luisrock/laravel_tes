<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use App\Policies\CollectionPolicy;
use App\Services\CollectionService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('registered', 'web');

    Config::set('subscription.tier_product_ids', ['prod_pro', 'prod_premium']);
    Config::set('subscription.tier_labels', ['prod_pro' => 'PRO', 'prod_premium' => 'PREMIUM']);
});

// ==========================================
// CollectionService — getLimitsForUser
// ==========================================

describe('CollectionService — getLimitsForUser', function () {

    it('retorna limites de registrado para usuário sem assinatura', function () {
        $user = User::factory()->create();
        $limits = app(CollectionService::class)->getLimitsForUser($user);

        expect($limits['max_collections'])->toBe(3)
            ->and($limits['max_items'])->toBe(15)
            ->and($limits['can_be_private'])->toBeFalse();
    });

    it('retorna limites de PRO para usuário PRO', function () {
        $user = createSubscribedUser('prod_pro');
        $limits = app(CollectionService::class)->getLimitsForUser($user);

        expect($limits['max_collections'])->toBe(10)
            ->and($limits['max_items'])->toBe(50)
            ->and($limits['can_be_private'])->toBeTrue();
    });

    it('retorna limites ilimitados para usuário PREMIUM', function () {
        $user = createSubscribedUser('prod_premium');
        $limits = app(CollectionService::class)->getLimitsForUser($user);

        expect($limits['max_collections'])->toBe(-1)
            ->and($limits['max_items'])->toBe(-1)
            ->and($limits['can_be_private'])->toBeTrue();
    });

    it('registrado não pode tornar coleção privada (can_be_private = false)', function () {
        $user = User::factory()->create();
        $limits = app(CollectionService::class)->getLimitsForUser($user);

        expect($limits['can_be_private'])->toBeFalse();
    });

});

// ==========================================
// CollectionService — canCreateCollection
// ==========================================

describe('CollectionService — canCreateCollection', function () {

    it('registrado pode criar coleção quando abaixo do limite', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(2)->create();

        expect(app(CollectionService::class)->canCreateCollection($user))->toBeTrue();
    });

    it('registrado não pode criar coleção quando atingiu o limite (3)', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        expect(app(CollectionService::class)->canCreateCollection($user))->toBeFalse();
    });

    it('PRO pode criar coleção quando abaixo do limite', function () {
        $user = createSubscribedUser('prod_pro');
        Collection::factory()->for($user)->count(9)->create();

        expect(app(CollectionService::class)->canCreateCollection($user))->toBeTrue();
    });

    it('PRO não pode criar coleção quando atingiu o limite (10)', function () {
        $user = createSubscribedUser('prod_pro');
        Collection::factory()->for($user)->count(10)->create();

        expect(app(CollectionService::class)->canCreateCollection($user))->toBeFalse();
    });

    it('PREMIUM pode sempre criar coleção (ilimitado)', function () {
        $user = createSubscribedUser('prod_premium');
        Collection::factory()->for($user)->count(100)->create();

        expect(app(CollectionService::class)->canCreateCollection($user))->toBeTrue();
    });

});

// ==========================================
// CollectionService — canAddItem
// ==========================================

describe('CollectionService — canAddItem', function () {

    it('registrado pode adicionar item quando abaixo do limite', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->count(14)
            ->sequence(fn ($seq) => ['content_id' => $seq->index + 1, 'content_type' => 'tese', 'tribunal' => 'stf'])
            ->create();

        expect(app(CollectionService::class)->canAddItem($user, $collection))->toBeTrue();
    });

    it('registrado não pode adicionar item quando atingiu o limite (15)', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->count(15)
            ->sequence(fn ($seq) => ['content_id' => $seq->index + 1, 'content_type' => 'tese', 'tribunal' => 'stf'])
            ->create();

        expect(app(CollectionService::class)->canAddItem($user, $collection))->toBeFalse();
    });

    it('PREMIUM pode sempre adicionar itens (ilimitado)', function () {
        $user = createSubscribedUser('prod_premium');
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->count(200)
            ->sequence(fn ($seq) => ['content_id' => $seq->index + 1, 'content_type' => 'tese', 'tribunal' => 'stf'])
            ->create();

        expect(app(CollectionService::class)->canAddItem($user, $collection))->toBeTrue();
    });

});

// ==========================================
// CollectionService — getUserCollectionsWithItemStatus
// ==========================================

describe('CollectionService — getUserCollectionsWithItemStatus', function () {

    it('marca has_item=true para coleção que contém o conteúdo', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        CollectionItem::factory()->for($collection)->create([
            'content_type' => 'tese',
            'content_id' => 42,
            'tribunal' => 'stf',
        ]);

        $result = app(CollectionService::class)
            ->getUserCollectionsWithItemStatus($user, 'tese', 42, 'stf');

        expect($result)->toHaveCount(1)
            ->and($result->first()->has_item)->toBeTrue();
    });

    it('marca has_item=false para coleção que não contém o conteúdo', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->create();

        $result = app(CollectionService::class)
            ->getUserCollectionsWithItemStatus($user, 'tese', 99, 'stf');

        expect($result)->toHaveCount(1)
            ->and($result->first()->has_item)->toBeFalse();
    });

    it('retorna apenas coleções do próprio usuário', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Collection::factory()->for($userA)->count(2)->create();
        Collection::factory()->for($userB)->count(3)->create();

        $result = app(CollectionService::class)
            ->getUserCollectionsWithItemStatus($userA, 'tese', 1, 'stf');

        expect($result)->toHaveCount(2);
    });

    it('não confunde has_item de usuários diferentes com o mesmo conteúdo', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $collectionA = Collection::factory()->for($userA)->create();
        Collection::factory()->for($userB)->create();

        CollectionItem::factory()->for($collectionA)->create([
            'content_type' => 'tese',
            'content_id' => 10,
            'tribunal' => 'stf',
        ]);

        // userB não tem o item — verificamos a perspectiva de userB
        $result = app(CollectionService::class)
            ->getUserCollectionsWithItemStatus($userB, 'tese', 10, 'stf');

        expect($result->first()->has_item)->toBeFalse();
    });

});

// ==========================================
// CollectionPolicy — view
// ==========================================

describe('CollectionPolicy — view', function () {

    it('coleção pública é visível para visitante não autenticado', function () {
        $collection = Collection::factory()->create(['is_private' => false]);

        expect(app(CollectionPolicy::class)->view(null, $collection))->toBeTrue();
    });

    it('coleção pública é visível para qualquer usuário autenticado', function () {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $collection = Collection::factory()->for($owner)->create(['is_private' => false]);

        expect(app(CollectionPolicy::class)->view($visitor, $collection))->toBeTrue();
    });

    it('coleção privada é visível apenas para o dono', function () {
        $owner = User::factory()->create();
        $collection = Collection::factory()->for($owner)->private()->create();

        expect(app(CollectionPolicy::class)->view($owner, $collection))->toBeTrue();
    });

    it('coleção privada não é visível para outro usuário', function () {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $collection = Collection::factory()->for($owner)->private()->create();

        expect(app(CollectionPolicy::class)->view($visitor, $collection))->toBeFalse();
    });

    it('coleção privada não é visível para visitante não autenticado', function () {
        $collection = Collection::factory()->private()->create();

        expect(app(CollectionPolicy::class)->view(null, $collection))->toBeFalse();
    });

});

// ==========================================
// CollectionPolicy — create
// ==========================================

describe('CollectionPolicy — create', function () {

    it('permite criar quando usuário está abaixo do limite', function () {
        $user = User::factory()->create();

        expect(app(CollectionPolicy::class)->create($user))->toBeTrue();
    });

    it('bloqueia criar quando usuário atingiu o limite', function () {
        $user = User::factory()->create();
        Collection::factory()->for($user)->count(3)->create();

        expect(app(CollectionPolicy::class)->create($user))->toBeFalse();
    });

});

// ==========================================
// CollectionPolicy — update / delete
// ==========================================

describe('CollectionPolicy — update e delete', function () {

    it('dono pode editar sua própria coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        expect(app(CollectionPolicy::class)->update($user, $collection))->toBeTrue();
    });

    it('outro usuário não pode editar a coleção', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($owner)->create();

        expect(app(CollectionPolicy::class)->update($other, $collection))->toBeFalse();
    });

    it('dono pode excluir sua própria coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        expect(app(CollectionPolicy::class)->delete($user, $collection))->toBeTrue();
    });

    it('outro usuário não pode excluir a coleção', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($owner)->create();

        expect(app(CollectionPolicy::class)->delete($other, $collection))->toBeFalse();
    });

});

// ==========================================
// CollectionPolicy — addItem / removeItem
// ==========================================

describe('CollectionPolicy — addItem e removeItem', function () {

    it('dono pode adicionar item quando abaixo do limite', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        expect(app(CollectionPolicy::class)->addItem($user, $collection))->toBeTrue();
    });

    it('dono não pode adicionar item quando atingiu o limite', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();
        CollectionItem::factory()->for($collection)->count(15)
            ->sequence(fn ($seq) => ['content_id' => $seq->index + 1, 'content_type' => 'tese', 'tribunal' => 'stf'])
            ->create();

        expect(app(CollectionPolicy::class)->addItem($user, $collection))->toBeFalse();
    });

    it('outro usuário não pode adicionar item na coleção alheia', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($owner)->create();

        expect(app(CollectionPolicy::class)->addItem($other, $collection))->toBeFalse();
    });

    it('dono pode remover item da coleção', function () {
        $user = User::factory()->create();
        $collection = Collection::factory()->for($user)->create();

        expect(app(CollectionPolicy::class)->removeItem($user, $collection))->toBeTrue();
    });

    it('outro usuário não pode remover item da coleção alheia', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($owner)->create();

        expect(app(CollectionPolicy::class)->removeItem($other, $collection))->toBeFalse();
    });

});
