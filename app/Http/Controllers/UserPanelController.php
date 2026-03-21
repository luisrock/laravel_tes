<?php

namespace App\Http\Controllers;

use App\Models\ContentView;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserPanelController extends Controller
{
    /**
     * Dashboard do painel do usuário (visão geral).
     */
    public function dashboard(): View
    {
        $recentViews = ContentView::forUser(auth()->id())
            ->orderByDesc('viewed_at')
            ->limit(5)
            ->get();

        $recentViews = $this->enrichViewsWithTeseData($recentViews);

        return view('user-panel.dashboard', [
            'recentViews' => $recentViews,
        ]);
    }

    /**
     * Página de perfil (dados, senha, 2FA).
     */
    public function profile(): View
    {
        return view('user-panel.profile');
    }

    /**
     * Histórico completo de visualizações paginado.
     */
    public function history(): View
    {
        $views = ContentView::forUser(auth()->id())
            ->orderByDesc('viewed_at')
            ->paginate(20);

        $views->setCollection($this->enrichViewsWithTeseData($views->getCollection()));

        return view('user-panel.history', [
            'views' => $views,
        ]);
    }

    /**
     * Enriquece views com tema/URL da tese (uma query por tribunal, evita N+1).
     *
     * @param  Collection<int, ContentView>  $views
     * @return Collection<int, ContentView>
     */
    private function enrichViewsWithTeseData(Collection $views): Collection
    {
        if ($views->isEmpty()) {
            return $views;
        }

        /** @var Collection<string, Collection<int|string, \stdClass>> $rowsByTribunal */
        $rowsByTribunal = collect();

        foreach ($views->groupBy(fn (ContentView $v) => strtolower($v->tribunal)) as $tribunalLower => $group) {
            $table = $this->getTableForTribunal((string) $tribunalLower);
            if ($table === null) {
                continue;
            }

            $ids = $group->pluck('content_id')->unique()->values()->all();

            try {
                $rows = DB::table($table)->whereIn('id', $ids)->get()->keyBy('id');
                $rowsByTribunal->put((string) $tribunalLower, $rows);
            } catch (\Exception $e) {
                Log::debug('Não foi possível carregar teses para enriquecimento em lote', [
                    'tribunal' => $tribunalLower,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $views->map(function (ContentView $view) use ($rowsByTribunal): ContentView {
            $tribunalLower = strtolower($view->tribunal);
            $rows = $rowsByTribunal->get($tribunalLower);

            if ($rows !== null) {
                $tese = $rows->get($view->content_id);

                if ($tese) {
                    $view->tema_texto = $tese->tema_texto ?? $tese->tema ?? '';
                    $view->tese_numero = $tese->numero ?? null;
                    $view->tese_url = '/tese/'.$view->tribunal.'/'.($tese->numero ?? $view->content_id);
                }
            }

            $view->tribunal_label = strtoupper($view->tribunal);

            return $view;
        });
    }

    private function getTableForTribunal(string $tribunal): ?string
    {
        return match (strtolower($tribunal)) {
            'stf' => 'stf_teses',
            'stj' => 'stj_teses',
            'tst' => 'tst_teses',
            'tnu' => 'tnu_teses',
            default => null,
        };
    }
}
