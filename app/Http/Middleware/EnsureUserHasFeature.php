<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasFeature
{
    /**
     * Handle an incoming request.
     *
     * Uso: Route::get('/rota', [Controller::class, 'method'])->middleware('feature:no_ads');
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Voce precisa estar logado para acessar esta pagina.');
        }

        if (! $user->hasFeature($featureKey)) {
            return redirect()->route('subscription.plans')
                ->with('info', 'Esta funcionalidade requer um plano que inclua: '.$this->getFeatureLabel($featureKey));
        }

        return $next($request);
    }

    protected function getFeatureLabel(string $featureKey): string
    {
        return match ($featureKey) {
            'no_ads' => 'Navegacao sem anuncios',
            'exclusive_content' => 'Conteudo exclusivo',
            'ai_tools' => 'Ferramentas de IA',
            default => $featureKey,
        };
    }
}
