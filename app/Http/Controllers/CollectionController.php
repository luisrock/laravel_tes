<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionItemRequest;
use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Services\CollectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function __construct(protected CollectionService $collectionService) {}

    /**
     * Lista as coleções do usuário autenticado.
     */
    public function index(Request $request): View
    {
        $collections = $request->user()
            ->collections()
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get();

        $limits = $this->collectionService->getLimitsForUser($request->user());

        $canCreate = $limits['max_collections'] === -1
            || $collections->count() < $limits['max_collections'];

        return view('colecoes.index', [
            'collections' => $collections,
            'limits' => $limits,
            'canCreate' => $canCreate,
        ]);
    }

    /**
     * Cria uma nova coleção.
     */
    public function store(StoreCollectionRequest $request): RedirectResponse
    {
        $this->authorize('create', Collection::class);

        $user = $request->user();
        $limits = $this->collectionService->getLimitsForUser($user);

        $isPrivate = $request->boolean('is_private') && $limits['can_be_private'];

        $collection = Collection::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_private' => $isPrivate,
        ]);

        return redirect()
            ->route('colecoes.edit', $collection->id)
            ->with('success', 'Coleção criada com sucesso.');
    }

    /**
     * Formulário de edição da coleção.
     */
    public function edit(Request $request, int $id): View
    {
        $collection = Collection::where('user_id', $request->user()->id)
            ->with('items')
            ->findOrFail($id);

        $this->authorize('update', $collection);

        $limits = $this->collectionService->getLimitsForUser($request->user());

        return view('colecoes.edit', [
            'collection' => $collection,
            'limits' => $limits,
        ]);
    }

    /**
     * Atualiza título, descrição e privacidade da coleção.
     */
    public function update(UpdateCollectionRequest $request, int $id): RedirectResponse
    {
        $collection = Collection::where('user_id', $request->user()->id)->findOrFail($id);

        $this->authorize('update', $collection);

        $limits = $this->collectionService->getLimitsForUser($request->user());
        $isPrivate = $request->boolean('is_private') && $limits['can_be_private'];

        $collection->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_private' => $isPrivate,
        ]);

        return redirect()
            ->route('colecoes.edit', $collection->id)
            ->with('success', 'Coleção atualizada com sucesso.');
    }

    /**
     * Exclui a coleção e todos os seus itens (cascade).
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $collection = Collection::where('user_id', $request->user()->id)->findOrFail($id);

        $this->authorize('delete', $collection);

        $collection->delete();

        return redirect()
            ->route('colecoes.index')
            ->with('success', 'Coleção excluída.');
    }

    /**
     * Adiciona um item (tese ou súmula) à coleção.
     */
    public function storeItem(StoreCollectionItemRequest $request, int $id): RedirectResponse
    {
        $collection = Collection::where('user_id', $request->user()->id)->findOrFail($id);

        $this->authorize('addItem', $collection);

        $alreadyExists = $collection->hasItem(
            $request->input('content_type'),
            (int) $request->input('content_id'),
            $request->input('tribunal')
        );

        if (! $alreadyExists) {
            $nextOrder = $collection->items()->max('order') + 1;

            CollectionItem::create([
                'collection_id' => $collection->id,
                'content_type' => $request->input('content_type'),
                'content_id' => $request->input('content_id'),
                'tribunal' => $request->input('tribunal'),
                'order' => $nextOrder,
            ]);
        }

        return back()->with('success', 'Item adicionado à coleção.');
    }

    /**
     * Remove um item da coleção.
     */
    public function destroyItem(Request $request, int $id, int $itemId): RedirectResponse
    {
        $collection = Collection::where('user_id', $request->user()->id)->findOrFail($id);

        $this->authorize('removeItem', $collection);

        CollectionItem::where('collection_id', $collection->id)
            ->where('id', $itemId)
            ->delete();

        return back()->with('success', 'Item removido da coleção.');
    }

    /**
     * Reordena os itens da coleção.
     *
     * Espera: { "order": [1, 5, 3, 2] } — array de IDs na nova ordem.
     */
    public function reorderItems(Request $request, int $id): RedirectResponse
    {
        $collection = Collection::where('user_id', $request->user()->id)->findOrFail($id);

        $this->authorize('update', $collection);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $itemIds = $request->input('order');

        foreach ($itemIds as $position => $itemId) {
            CollectionItem::where('collection_id', $collection->id)
                ->where('id', $itemId)
                ->update(['order' => $position]);
        }

        return back()->with('success', 'Ordem atualizada.');
    }
}
