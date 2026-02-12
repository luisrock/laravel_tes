<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionConfigured
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tierProductIds = config('subscription.tier_product_ids', []);
        $stripeKey = config('cashier.key');
        $stripeSecret = config('cashier.secret');

        $isConfigured = ! empty($tierProductIds) && ! empty($stripeKey) && ! empty($stripeSecret);

        if ($isConfigured) {
            return $next($request);
        }

        $message = 'Assinaturas indisponiveis temporariamente. Tente novamente mais tarde.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 503);
        }

        return redirect()->route('searchpage')->with('info', $message);
    }
}
