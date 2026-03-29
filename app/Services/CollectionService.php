<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Collection as LaravelCollection;

class CollectionService
{
    /**
     * Limites padrão, usados enquanto o admin ainda não configurou via Filament (Etapa 3).
     *
     * @var array<string, int>
     */
    private const DEFAULTS = [
        'registered_max' => 3,
        'registered_items_max' => 15,
        'pro_max' => 10,
        'pro_items_max' => 50,
        'premium_max' => -1,        // -1 = ilimitado
        'premium_items_max' => -1,  // -1 = ilimitado
    ];

    /**
     * Retorna os limites e capacidade de privacidade do usuário conforme o seu tier.
     *
     * @return array{max_collections: int, max_items: int, can_be_private: bool}
     */
    public function getLimitsForUser(User $user): array
    {
        return match ($this->getUserTier($user)) {
            'admin' => [
                'max_collections' => -1,
                'max_items' => -1,
                'can_be_private' => true,
            ],
            'premium' => [
                'max_collections' => (int) SiteSetting::get('collections_premium_max', self::DEFAULTS['premium_max']),
                'max_items' => (int) SiteSetting::get('collections_premium_items_max', self::DEFAULTS['premium_items_max']),
                'can_be_private' => true,
            ],
            'pro' => [
                'max_collections' => (int) SiteSetting::get('collections_pro_max', self::DEFAULTS['pro_max']),
                'max_items' => (int) SiteSetting::get('collections_pro_items_max', self::DEFAULTS['pro_items_max']),
                'can_be_private' => true,
            ],
            default => [
                'max_collections' => (int) SiteSetting::get('collections_registered_max', self::DEFAULTS['registered_max']),
                'max_items' => (int) SiteSetting::get('collections_registered_items_max', self::DEFAULTS['registered_items_max']),
                'can_be_private' => false,
            ],
        };
    }

    /**
     * Verifica se o usuário ainda pode criar uma nova coleção dentro do limite do seu tier.
     */
    public function canCreateCollection(User $user): bool
    {
        $limits = $this->getLimitsForUser($user);

        if ($limits['max_collections'] === -1) {
            return true;
        }

        return $user->collections()->count() < $limits['max_collections'];
    }

    /**
     * Verifica se o usuário ainda pode adicionar itens à coleção dentro do limite do seu tier.
     */
    public function canAddItem(User $user, Collection $collection): bool
    {
        $limits = $this->getLimitsForUser($user);

        if ($limits['max_items'] === -1) {
            return true;
        }

        return $collection->items()->count() < $limits['max_items'];
    }

    /**
     * Retorna as coleções do usuário com flag `has_item` indicando se o conteúdo já está salvo.
     * Executa 2 queries (sem N+1).
     *
     * @return LaravelCollection<int, Collection>
     */
    public function getUserCollectionsWithItemStatus(
        User $user,
        string $contentType,
        int $contentId,
        string $tribunal
    ): LaravelCollection {
        $collectionIdsWithItem = CollectionItem::where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('tribunal', $tribunal)
            ->whereIn('collection_id', $user->collections()->select('id'))
            ->pluck('collection_id')
            ->all();

        return $user->collections()
            ->get()
            ->map(function (Collection $collection) use ($collectionIdsWithItem): Collection {
                $collection->setAttribute('has_item', in_array($collection->id, $collectionIdsWithItem, true));

                return $collection;
            });
    }

    /**
     * Retorna o tier do usuário: 'admin', 'premium', 'pro' ou 'registered'.
     *
     * Prioridade: role admin > Stripe plan > Spatie role (subscriber/premium) > registered.
     * Os Spatie roles são usados para testes via debug bar e em ambientes sem Stripe.
     */
    private function getUserTier(User $user): string
    {
        if ($user->hasRole('admin')) {
            return 'admin';
        }

        $plan = $user->getSubscriptionPlan();
        $labels = config('subscription.tier_labels', []);

        $tierFromStripe = match ($labels[$plan] ?? null) {
            'PREMIUM' => 'premium',
            'PRO' => 'pro',
            default => null,
        };

        if ($tierFromStripe !== null) {
            return $tierFromStripe;
        }

        if ($user->hasRole('premium')) {
            return 'premium';
        }

        if ($user->hasRole('subscriber')) {
            return 'pro';
        }

        return 'registered';
    }
}
