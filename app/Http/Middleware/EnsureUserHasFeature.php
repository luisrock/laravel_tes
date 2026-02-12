<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasFeature
{
    /**
     * Handle an incoming request.
     *
     * Uso: Route::get('/rota', [Controller::class, 'method'])->middleware('feature:no_ads');
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $featureKey)
    {
        $user = $request->user();

        // Se não está logado, redirecionar para login
        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        // Se não tem a feature, redirecionar para página de planos
        if (! $user->hasFeature($featureKey)) {
            return redirect()->route('subscription.plans')
                ->with('info', 'Esta funcionalidade requer um plano que inclua: '.$this->getFeatureLabel($featureKey));
        }

        return $next($request);
    }

    /**
     * Retorna label amigável para a feature.
     */
    protected function getFeatureLabel(string $featureKey): string
    {
        return match ($featureKey) {
            'no_ads' => 'Navegação sem anúncios',
            'exclusive_content' => 'Conteúdo exclusivo',
            'ai_tools' => 'Ferramentas de IA',
            default => $featureKey,
        };
    }
}
