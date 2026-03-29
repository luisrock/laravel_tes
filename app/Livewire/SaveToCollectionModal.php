<?php

namespace App\Livewire;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Services\CollectionService;
use App\Services\SearchTribunalRegistry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class SaveToCollectionModal extends Component
{
    use AuthorizesRequests;

    public bool $isOpen = false;

    public string $contentType = '';

    public string $tribunal = '';

    public int $contentId = 0;

    public string $newTitle = '';

    public bool $showCreate = false;

    public bool $canCreate = false;

    public int $justToggledId = 0;

    /** @var array<int, array{id: int, title: string, has_item: bool, is_full: bool}> */
    public array $collections = [];

    public function open(string $type, string $tribunal, int $contentId): void
    {
        $this->contentType = $type;
        $this->tribunal = $tribunal;
        $this->contentId = $contentId;
        $this->isOpen = true;
        $this->showCreate = false;
        $this->newTitle = '';
        $this->justToggledId = 0;

        $this->loadCollections();
    }

    public function toggle(int $collectionId): void
    {
        $this->validateTribunal();

        $user = Auth::user();
        $collection = Collection::where('user_id', $user->id)->find($collectionId);
        abort_unless($collection !== null, 403);

        $existing = CollectionItem::where('collection_id', $collectionId)
            ->where('content_type', $this->contentType)
            ->where('content_id', $this->contentId)
            ->where('tribunal', $this->tribunal)
            ->first();

        if ($existing) {
            $this->authorize('removeItem', $collection);
            $existing->delete();
        } else {
            $this->authorize('addItem', $collection);

            CollectionItem::create([
                'collection_id' => $collectionId,
                'content_type' => $this->contentType,
                'content_id' => $this->contentId,
                'tribunal' => $this->tribunal,
                'order' => $collection->items()->count(),
            ]);
        }

        $this->justToggledId = $collectionId;
        $this->loadCollections();
        $this->dispatch('item-toggled');
    }

    public function createAndAdd(): void
    {
        $this->validateTribunal();
        $this->authorize('create', Collection::class);

        $this->validate(['newTitle' => ['required', 'string', 'min:2', 'max:100']]);

        $user = Auth::user();

        $collection = Collection::create([
            'user_id' => $user->id,
            'title' => $this->newTitle,
            'is_private' => false,
        ]);

        CollectionItem::create([
            'collection_id' => $collection->id,
            'content_type' => $this->contentType,
            'content_id' => $this->contentId,
            'tribunal' => $this->tribunal,
            'order' => 0,
        ]);

        $this->newTitle = '';
        $this->showCreate = false;
        $this->canCreate = app(CollectionService::class)->canCreateCollection($user);

        $this->loadCollections();
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    /**
     * Valida que o tribunal é uma sigla conhecida.
     */
    private function validateTribunal(): void
    {
        $valid = array_map('strtolower', app(SearchTribunalRegistry::class)->keys());
        abort_unless(in_array($this->tribunal, $valid, true), 422);
    }

    private function loadCollections(): void
    {
        $user = Auth::user();
        $limits = app(CollectionService::class)->getLimitsForUser($user);

        // query 1: coleções + total de itens por coleção
        $collections = $user->collections()->withCount('items')->get();

        $this->canCreate = $limits['max_collections'] === -1
            || $collections->count() < $limits['max_collections'];

        // query 2: coleções que já contêm o item específico
        $idsComItem = CollectionItem::where('content_type', $this->contentType)
            ->where('content_id', $this->contentId)
            ->where('tribunal', $this->tribunal)
            ->whereIn('collection_id', $collections->pluck('id'))
            ->pluck('collection_id')
            ->all();

        $this->collections = $collections->map(function (Collection $collection) use ($limits, $idsComItem): array {
            $hasItem = in_array($collection->id, $idsComItem, true);
            $isFull = $limits['max_items'] !== -1
                && $collection->items_count >= $limits['max_items']
                && ! $hasItem;

            return [
                'id' => $collection->id,
                'title' => $collection->title,
                'has_item' => $hasItem,
                'is_full' => $isFull,
            ];
        })->values()->all();
    }

    public function render(): View
    {
        return view('livewire.save-to-collection-modal');
    }
}
