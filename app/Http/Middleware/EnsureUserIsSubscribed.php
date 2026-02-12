<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Se não está logado, redirecionar para login
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        // Se não é assinante, redirecionar para página de planos
        if (!$user->isSubscriber()) {
            return redirect()->route('subscription.plans')
                ->with('info', 'Esta página é exclusiva para assinantes.');
        }

        return $next($request);
    }
}
