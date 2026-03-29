<?php

namespace App\Livewire;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Services\CollectionService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CollectionEdit extends Component
{
    use AuthorizesRequests;

    public int $collectionId;

    public string $title = '';

    public string $description = '';

    public bool $isPrivate = false;

    /** @var array{max_collections: int, max_items: int, can_be_private: bool} */
    public array $limits = [];

    public function mount(int $collectionId): void
    {
        $collection = Collection::where('user_id', Auth::id())->find($collectionId);
        abort_unless($collection !== null, 404);
        $this->authorize('update', $collection);

        $this->collectionId = $collectionId;
        $this->title = $collection->title;
        $this->description = $collection->description ?? '';
        $this->isPrivate = $collection->is_private;
        $this->limits = app(CollectionService::class)->getLimitsForUser(Auth::user());
    }

    public function save(): void
    {
        $collection = Collection::where('user_id', Auth::id())->findOrFail($this->collectionId);
        $this->authorize('update', $collection);

        $this->validate([
            'title' => ['required', 'string', 'min:2', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $isPrivate = $this->isPrivate && $this->limits['can_be_private'];

        $collection->update([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'is_private' => $isPrivate,
        ]);

        $this->isPrivate = $isPrivate;
        $this->dispatch('collection-saved');
    }

    public function removeItem(int $itemId): void
    {
        $collection = Collection::where('user_id', Auth::id())->findOrFail($this->collectionId);
        $this->authorize('removeItem', $collection);

        CollectionItem::where('collection_id', $collection->id)
            ->where('id', $itemId)
            ->delete();
    }

    public function reorderItems(array $order): void
    {
        $collection = Collection::where('user_id', Auth::id())->findOrFail($this->collectionId);
        $this->authorize('update', $collection);

        foreach ($order as $position => $itemId) {
            CollectionItem::where('collection_id', $collection->id)
                ->where('id', (int) $itemId)
                ->update(['order' => $position]);
        }

        $this->dispatch('reorder-saved');
    }

    public function deleteCollection(): void
    {
        $collection = Collection::where('user_id', Auth::id())->findOrFail($this->collectionId);
        $this->authorize('delete', $collection);
        $collection->delete();

        $this->redirect(route('colecoes.index'));
    }

    public function render()
    {
        $collection = Collection::where('user_id', Auth::id())
            ->with(['items', 'user'])
            ->findOrFail($this->collectionId);

        $itemsWithContent = $collection->items->map(function (CollectionItem $item): object {
            try {
                $content = $item->getContent();
            } catch (QueryException) {
                $content = null;
            }

            return (object) [
                'id' => $item->id,
                'content_type' => $item->content_type,
                'tribunal' => strtoupper($item->tribunal),
                'tribunal_raw' => $item->tribunal,
                'content_id' => $item->content_id,
                'label' => CollectionItem::resolveLabel($item->content_type, $content),
            ];
        });

        return view('livewire.collection-edit', [
            'collection' => $collection,
            'itemsWithContent' => $itemsWithContent,
        ]);
    }
}
