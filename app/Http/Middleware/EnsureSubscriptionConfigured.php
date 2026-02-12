<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionConfigured
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
        $tierProductIds = config('subscription.tier_product_ids', []);
        $stripeKey = config('cashier.key');
        $stripeSecret = config('cashier.secret');

        $isConfigured = !empty($tierProductIds) && !empty($stripeKey) && !empty($stripeSecret);

        if ($isConfigured) {
            return $next($request);
        }

        $message = 'Assinaturas indisponíveis temporariamente. Tente novamente mais tarde.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 503);
        }

        return redirect()->route('searchpage')->with('info', $message);
    }
}
