<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BearerTokenMiddleware
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
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => 'Token de autenticação não fornecido.'
            ], 401);
        }

        // Aqui você pode implementar a validação do token
        // Por enquanto, vou usar uma validação simples
        // Você pode configurar o token no .env
        $validToken = env('API_TOKEN', 'your-secret-token-here');
        
        if ($token !== $validToken) {
            return response()->json([
                'success' => false,
                'error' => 'Token de autenticação inválido.'
            ], 401);
        }

        return $next($request);
    }
} 