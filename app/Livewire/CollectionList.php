<?php

namespace App\Livewire;

use App\Models\Collection;
use App\Services\CollectionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CollectionList extends Component
{
    use AuthorizesRequests;

    public bool $showCreateForm = false;

    public bool $showLimitCta = false;

    public string $newTitle = '';

    public string $newDescription = '';

    /** @var array{max_collections: int, max_items: int, can_be_private: bool} */
    public array $limits = [];

    public bool $canCreate = false;

    public function mount(): void
    {
        $service = app(CollectionService::class);
        $user = Auth::user();

        $this->limits = $service->getLimitsForUser($user);
        $this->canCreate = $service->canCreateCollection($user);
    }

    public function openCreateForm(): void
    {
        if (! $this->canCreate) {
            $this->showLimitCta = true;

            return;
        }

        $this->showLimitCta = false;
        $this->showCreateForm = true;
        $this->reset(['newTitle', 'newDescription']);
        $this->resetValidation();
    }

    public function cancelCreate(): void
    {
        $this->showCreateForm = false;
        $this->showLimitCta = false;
        $this->reset(['newTitle', 'newDescription']);
        $this->resetValidation();
    }

    public function createCollection(): void
    {
        $this->authorize('create', Collection::class);

        $this->validate([
            'newTitle' => ['required', 'string', 'min:2', 'max:100'],
            'newDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $collection = Collection::create([
            'user_id' => Auth::id(),
            'title' => $this->newTitle,
            'description' => $this->newDescription ?: null,
            'is_private' => false,
        ]);

        $this->redirect(route('colecoes.edit', $collection->id));
    }

    public function deleteCollection(int $id): void
    {
        $collection = Collection::where('user_id', Auth::id())->find($id);
        abort_unless($collection !== null, 403);

        $this->authorize('delete', $collection);
        $collection->delete();

        $service = app(CollectionService::class);
        $this->canCreate = $service->canCreateCollection(Auth::user());
    }

    public function render()
    {
        return view('livewire.collection-list', [
            'collections' => Auth::user()
                ->collections()
                ->withCount('items')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
