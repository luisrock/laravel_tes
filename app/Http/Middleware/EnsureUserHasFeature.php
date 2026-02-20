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
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Autenticacao necessaria',
                    'message' => 'Voce precisa estar logado para acessar esta funcionalidade.',
                    'is_paywall' => true,
                    'redirect_url' => '/login',
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Voce precisa estar logado para acessar esta pagina.');
        }

        if (! $user->hasFeature($featureKey)) {
            $featureLabel = $this->getFeatureLabel($featureKey);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Acesso premium necessario',
                    'message' => 'Esta funcionalidade requer um plano que inclua: '.$featureLabel.'. Desbloqueie agora para ter acesso completo.',
                    'is_paywall' => true,
                    'redirect_url' => '/assinar',
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('info', 'Esta funcionalidade requer um plano que inclua: '.$featureLabel);
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
