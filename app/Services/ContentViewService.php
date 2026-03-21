<?php

namespace App\Services;

use App\Models\ContentView;
use App\Models\SiteSetting;
use App\Models\User;

class ContentViewService
{
    /**
     * Registra uma visualização de conteúdo premium. Idempotente: se o
     * mesmo conteúdo já foi visualizado pelo usuário nas últimas 24h,
     * não cria um novo registro.
     *
     * Retorna true se criou um novo registro, false se já existia.
     */
    public function recordView(User $user, string $contentType, int $contentId, string $tribunal): bool
    {
        $exists = ContentView::forUser($user->id)
            ->forContent($contentType, $contentId, $tribunal)
            ->inLast24Hours()
            ->exists();

        if ($exists) {
            return false;
        }

        ContentView::create([
            'user_id' => $user->id,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'tribunal' => $tribunal,
            'viewed_at' => now(),
        ]);

        return true;
    }

    /**
     * Verifica se o metered wall está ativo.
     */
    public function isMeteredWallEnabled(): bool
    {
        return SiteSetting::getAsBool('metered_wall_enabled', true);
    }

    /**
     * Retorna o limite diário de views para o usuário, considerando seu tier.
     * null = ilimitado (subscriber, premium, admin).
     */
    public function getDailyLimit(User $user): ?int
    {
        if ($user->hasRole('admin') || $user->isSubscriber() || $user->hasAnyRole(['subscriber', 'premium'])) {
            return null;
        }

        return (int) SiteSetting::get('metered_wall_daily_limit', '3');
    }

    /**
     * Verifica se o usuário atingiu o limite diário de views.
     * Admin/subscriber/premium nunca atingem.
     */
    public function hasReachedDailyLimit(User $user): bool
    {
        $limit = $this->getDailyLimit($user);

        if ($limit === null) {
            return false;
        }

        $viewCount = $this->countDailyViews($user);

        return $viewCount > $limit;
    }

    /**
     * Retorna quantas views restam para o usuário hoje.
     * null = ilimitado.
     */
    public function remainingViews(User $user): ?int
    {
        $limit = $this->getDailyLimit($user);

        if ($limit === null) {
            return null;
        }

        $viewCount = $this->countDailyViews($user);

        return max(0, $limit - $viewCount);
    }

    /**
     * Conta views únicas do usuário nas últimas 24 horas.
     */
    private function countDailyViews(User $user): int
    {
        return ContentView::forUser($user->id)
            ->inLast24Hours()
            ->count();
    }
}
