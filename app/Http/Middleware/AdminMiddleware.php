<?php

namespace App\Http\Middleware;

use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request):((Response|RedirectResponse)) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!auth()->user() || !auth()->user()->hasAnyPermission($permissions)) {
            abort(403);
        }
    
        return $next($request);
    }
}

