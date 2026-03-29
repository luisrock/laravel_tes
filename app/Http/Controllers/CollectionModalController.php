<?php

namespace App\Http\Controllers;

use App\Services\CollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionModalController extends Controller
{
    public function __construct(protected CollectionService $collectionService) {}

    /**
     * Retorna as coleções do usuário com flag indicando se o conteúdo já está salvo.
     * Usado pelo modal "Salvar em Coleção" (Etapa 6).
     */
    public function show(Request $request, string $type, string $tribunal, int $contentId): JsonResponse
    {
        $user = $request->user();

        $collections = $this->collectionService->getUserCollectionsWithItemStatus(
            $user,
            $type,
            $contentId,
            $tribunal
        );

        $limits = $this->collectionService->getLimitsForUser($user);

        return response()->json([
            'collections' => $collections->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'has_item' => $c->has_item,
            ]),
            'can_create' => $this->collectionService->canCreateCollection($user),
            'can_be_private' => $limits['can_be_private'],
        ]);
    }
}
