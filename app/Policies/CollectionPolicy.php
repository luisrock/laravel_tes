<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use App\Services\CollectionService;

class CollectionPolicy
{
    public function __construct(protected CollectionService $collectionService) {}

    /**
     * Coleções públicas são visíveis para qualquer visitante.
     * Coleções privadas são visíveis apenas para o dono.
     */
    public function view(?User $user, Collection $collection): bool
    {
        if (! $collection->is_private) {
            return true;
        }

        return $user?->id === $collection->user_id;
    }

    /**
     * Qualquer usuário autenticado pode criar coleções, desde que não tenha atingido o limite.
     */
    public function create(User $user): bool
    {
        return $this->collectionService->canCreateCollection($user);
    }

    /**
     * Apenas o dono pode editar a coleção.
     */
    public function update(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    /**
     * Apenas o dono pode excluir a coleção.
     */
    public function delete(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    /**
     * Apenas o dono pode adicionar itens, desde que não tenha atingido o limite.
     */
    public function addItem(User $user, Collection $collection): bool
    {
        if ($user->id !== $collection->user_id) {
            return false;
        }

        return $this->collectionService->canAddItem($user, $collection);
    }

    /**
     * Apenas o dono pode remover itens da coleção.
     */
    public function removeItem(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }
}
