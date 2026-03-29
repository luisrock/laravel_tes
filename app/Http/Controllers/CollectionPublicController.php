<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\View\View;

class CollectionPublicController extends Controller
{
    /**
     * Exibe uma coleção pública. Coleções privadas retornam 403 para não-donos.
     */
    public function show(string $username, string $slug): View
    {
        $owner = User::where('name', $username)->firstOrFail();

        $collection = Collection::where('user_id', $owner->id)
            ->where('slug', $slug)
            ->with(['items' => fn ($q) => $q->orderBy('order'), 'user'])
            ->firstOrFail();

        $this->authorize('view', $collection);

        $items = $this->resolveItems($collection->items);

        return view('colecoes.show', [
            'collection' => $collection,
            'owner' => $owner,
            'items' => $items,
        ]);
    }

    /**
     * Resolve labels e URLs para cada item da coleção.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, CollectionItem>  $rawItems
     * @return SupportCollection<int, object>
     */
    private function resolveItems(\Illuminate\Database\Eloquent\Collection $rawItems): SupportCollection
    {
        return $rawItems->map(function (CollectionItem $item): object {
            try {
                $content = $item->getContent();
            } catch (QueryException) {
                $content = null;
            }

            return (object) [
                'id' => $item->id,
                'content_type' => $item->content_type,
                'tribunal' => $item->tribunal,
                'tribunal_upper' => strtoupper($item->tribunal),
                'content_id' => $item->content_id,
                'label' => CollectionItem::resolveLabel($item->content_type, $content),
                'url' => CollectionItem::resolveDetailUrl($item->content_type, $item->tribunal, $content),
                'content' => $content,
            ];
        });
    }
}
