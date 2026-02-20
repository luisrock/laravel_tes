<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSubscribed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
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

        if (! $user->isSubscriber()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Acesso premium necessario',
                    'message' => 'Esta funcionalidade e exclusiva para assinantes Pro e Premium. Desbloqueie agora para ter acesso completo.',
                    'is_paywall' => true,
                    'redirect_url' => '/assinar',
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('info', 'Esta pagina e exclusiva para assinantes.');
        }

        return $next($request);
    }
}
