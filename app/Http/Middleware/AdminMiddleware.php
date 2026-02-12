<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):((Response|RedirectResponse))  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (! auth()->user() || ! auth()->user()->hasAnyPermission($permissions)) {
            abort(403);
        }

        return $next($request);
    }
}
