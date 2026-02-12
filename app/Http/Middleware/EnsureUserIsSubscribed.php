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
            return redirect()->route('login')
                ->with('error', 'Voce precisa estar logado para acessar esta pagina.');
        }

        if (! $user->isSubscriber()) {
            return redirect()->route('subscription.plans')
                ->with('info', 'Esta pagina e exclusiva para assinantes.');
        }

        return $next($request);
    }
}
