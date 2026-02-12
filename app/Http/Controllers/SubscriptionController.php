<?php

namespace App\Http\Controllers;

use App\Models\StripeWebhookEvent;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
    ) {}

    /**
     * Pagina de planos/precos.
     */
    public function index(): View
    {
        $plans = $this->stripeService->getFormattedPlans();

        return view('subscription.plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Inicia checkout via Stripe Checkout.
     */
    public function checkout(Request $request): mixed
    {
        $request->validate([
            'priceId' => 'required|string',
        ]);

        $priceId = $request->input('priceId');
        $user = $request->user();

        if (! $this->stripeService->isValidPriceId($priceId)) {
            Log::warning('Tentativa de checkout com priceId invalido', [
                'user_id' => $user->id,
                'price_id' => $priceId,
            ]);

            return back()->with('error', 'Plano invalido selecionado.');
        }

        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if ($user->subscribed($subscriptionName)) {
            return $this->billingPortal($request);
        }

        try {
            return $user->newSubscription($subscriptionName, $priceId)
                ->allowPromotionCodes()
                ->checkout([
                    'success_url' => route('subscription.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('subscription.cancel'),
                    'client_reference_id' => (string) $user->id,
                ]);
        } catch (Exception $e) {
            Log::error('Erro ao criar sessao de checkout', [
                'user_id' => $user->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao iniciar checkout. Por favor, tente novamente.');
        }
    }

    /**
     * Pagina de sucesso apos checkout.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Sessao invalida.');
        }

        $isProcessed = StripeWebhookEvent::checkoutSessionProcessed($sessionId);

        return view('subscription.success', [
            'sessionId' => $sessionId,
            'isProcessed' => $isProcessed,
        ]);
    }

    /**
     * Pagina quando usuario cancela/desiste do checkout.
     */
    public function cancel(): View
    {
        return view('subscription.cancel');
    }

    /**
     * Pagina de status da assinatura do usuario.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        return view('subscription.show', [
            'user' => $user,
            'subscription' => $subscription,
            'isSubscriber' => $user->isSubscriber(),
            'isOnGracePeriod' => $user->isOnGracePeriod(),
            'accessEndsAt' => $user->getAccessEndsAt(),
            'planName' => $user->getSubscriptionPlan(),
        ]);
    }

    /**
     * Redireciona para Stripe Billing Portal.
     */
    public function billingPortal(Request $request): RedirectResponse
    {
        $user = $request->user();

        try {
            return $user->redirectToBillingPortal(route('subscription.show'));
        } catch (Exception $e) {
            Log::error('Erro ao redirecionar para Billing Portal', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao acessar portal. Por favor, tente novamente.');
        }
    }

    /**
     * Endpoint AJAX para verificar status de processamento do checkout.
     */
    public function checkProcessingStatus(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return response()->json(['status' => 'error', 'message' => 'Session ID required'], 400);
        }

        $isProcessed = StripeWebhookEvent::checkoutSessionProcessed($sessionId);

        return response()->json([
            'status' => $isProcessed ? 'completed' : 'processing',
        ]);
    }
}
