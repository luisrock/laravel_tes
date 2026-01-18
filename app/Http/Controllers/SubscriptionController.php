<?php

namespace App\Http\Controllers;

use App\Models\StripeWebhookEvent;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Página de planos/preços.
     */
    public function index()
    {
        $plans = $this->stripeService->getFormattedPlans();

        return view('subscription.plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Inicia checkout via Stripe Checkout.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'priceId' => 'required|string',
        ]);

        $priceId = $request->input('priceId');
        $user = $request->user();

        // Validar price ID contra allowlist
        if (!$this->stripeService->isValidPriceId($priceId)) {
            Log::warning('Tentativa de checkout com priceId inválido', [
                'user_id' => $user->id,
                'price_id' => $priceId,
            ]);
            return back()->with('error', 'Plano inválido selecionado.');
        }

        // Verificar se usuário já tem assinatura ativa
        $source = $user->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if ($source && $source->subscribed($subscriptionName)) {
            // Redirecionar para Billing Portal para upgrade/gerenciamento
            return $this->billingPortal($request);
        }

        // Criar sessão de checkout
        try {
            $checkoutSession = $user->newSubscription($subscriptionName, $priceId)
                ->checkout([
                    'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('subscription.cancel'),
                    'client_reference_id' => (string) $user->id,
                    'allow_promotion_codes' => true,
                ]);

            return redirect($checkoutSession->url);
        } catch (\Exception $e) {
            Log::error('Erro ao criar sessão de checkout', [
                'user_id' => $user->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Erro ao iniciar checkout. Por favor, tente novamente.');
        }
    }

    /**
     * Página de sucesso após checkout.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Sessão inválida.');
        }

        // Verificar se o webhook já processou esta sessão
        $isProcessed = StripeWebhookEvent::checkoutSessionProcessed($sessionId);

        return view('subscription.success', [
            'sessionId' => $sessionId,
            'isProcessed' => $isProcessed,
        ]);
    }

    /**
     * Página quando usuário cancela/desiste do checkout.
     */
    public function cancel()
    {
        return view('subscription.cancel');
    }

    /**
     * Página de status da assinatura do usuário.
     */
    public function show(Request $request)
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
    public function billingPortal(Request $request)
    {
        $user = $request->user();

        try {
            return $user->redirectToBillingPortal(route('subscription.show'));
        } catch (\Exception $e) {
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
    public function checkProcessingStatus(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => 'Session ID required'], 400);
        }

        $isProcessed = StripeWebhookEvent::checkoutSessionProcessed($sessionId);

        return response()->json([
            'status' => $isProcessed ? 'completed' : 'processing',
        ]);
    }
}
