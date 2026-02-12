<?php

namespace App\Http\Controllers;

use Exception;
use Stripe\StripeClient;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\WelcomeSubscriberNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Laravel\Cashier\Subscription;

class WebhookController extends CashierWebhookController
{
    /**
     * Handle incoming webhook.
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $eventId = $payload['id'] ?? null;
        $eventType = $payload['type'] ?? null;
        $data = $payload['data']['object'] ?? [];

        if (!$eventId || !$eventType) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Extrair IDs relevantes do payload
        $stripeObjectId = $this->extractObjectId($eventType, $data);
        $userId = $this->extractUserId($eventType, $data);

        // Padrão atômico: firstOrCreate evita race condition
        $webhookEvent = StripeWebhookEvent::firstOrCreate(
            ['stripe_event_id' => $eventId],
            [
                'event_type' => $eventType,
                'stripe_object_id' => $stripeObjectId,
                'user_id' => $userId,
                'received_at' => now(),
            ]
        );

        // Se já foi processado com sucesso, retorna early
        if ($webhookEvent->isProcessed()) {
            return response()->json(['status' => 'already_processed']);
        }

        // Se não foi recém-criado, é reprocessamento
        if (!$webhookEvent->wasRecentlyCreated) {
            $webhookEvent->increment('attempts');
        }

        try {
            // Delegar para o Cashier processar o webhook
            $response = parent::handleWebhook($request);

            // Marcar como processado após sucesso
            $webhookEvent->update([
                'processed_at' => now(),
                'failed_at' => null,
                'last_error' => null,
            ]);

            return $response;
        } catch (Exception $e) {
            $webhookEvent->update([
                'failed_at' => now(),
                'last_error' => $e->getMessage(),
            ]);

            Log::error('Webhook processing failed', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle checkout.session.completed event.
     */
    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'];
        $clientReferenceId = $session['client_reference_id'] ?? null;

        if ($clientReferenceId) {
            // Atualizar o webhook event com o user_id
            StripeWebhookEvent::where('stripe_object_id', $session['id'])
                ->whereNull('user_id')
                ->update(['user_id' => $clientReferenceId]);

            $user = User::find($clientReferenceId);
            if ($user) {
                try {
                    $user->notify(new WelcomeSubscriberNotification());
                } catch (Exception $e) {
                    Log::error('Erro ao enviar email de boas-vindas', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // O Cashier já gerencia a criação da subscription
        return $this->successMethod();
    }

    /**
     * Handle customer.subscription.created event.
     */
    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $subscription = $payload['data']['object'];
        $this->updateCurrentPeriodEnd($subscription);

        return parent::handleCustomerSubscriptionCreated($payload);
    }

    /**
     * Handle customer.subscription.updated event.
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $subscription = $payload['data']['object'];
        $this->updateCurrentPeriodEnd($subscription);

        $cancelAtPeriodEnd = $subscription['cancel_at_period_end'] ?? false;
        $previousCancelAtPeriodEnd = $payload['data']['previous_attributes']['cancel_at_period_end'] ?? null;

        if ($cancelAtPeriodEnd && $previousCancelAtPeriodEnd === false) {
            $userId = $this->extractUserId('customer.subscription.updated', $subscription);
            $user = $userId ? User::find($userId) : null;
            $endsAtTimestamp = $subscription['current_period_end'] ?? null;
            $endsAt = $endsAtTimestamp ? Carbon::createFromTimestamp($endsAtTimestamp) : null;

            if ($user) {
                try {
                    $user->notify(new SubscriptionCanceledNotification($endsAt));
                } catch (Exception $e) {
                    Log::error('Erro ao enviar email de cancelamento', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    /**
     * Handle invoice.payment_succeeded event.
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $invoice = $payload['data']['object'];
        $stripeSubscriptionId = $invoice['subscription'] ?? null;

        if ($stripeSubscriptionId) {
            // Buscar subscription no Stripe para obter current_period_end atualizado
            try {
                $stripe = new StripeClient(config('cashier.secret'));
                $stripeSubscription = $stripe->subscriptions->retrieve($stripeSubscriptionId);
                $this->updateCurrentPeriodEnd((array) $stripeSubscription);
            } catch (Exception $e) {
                Log::warning('Erro ao buscar subscription para atualizar current_period_end', [
                    'subscription_id' => $stripeSubscriptionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->successMethod();
    }

    /**
     * Atualiza current_period_end na subscription local.
     */
    protected function updateCurrentPeriodEnd(array $stripeSubscription): void
    {
        $stripeId = $stripeSubscription['id'] ?? null;
        $currentPeriodEnd = $stripeSubscription['current_period_end'] ?? null;

        if (!$stripeId || !$currentPeriodEnd) {
            return;
        }

        Subscription::where('stripe_id', $stripeId)->update([
            'current_period_end' => Carbon::createFromTimestamp($currentPeriodEnd),
        ]);
    }

    /**
     * Extrai o ID do objeto principal do evento.
     */
    protected function extractObjectId(string $eventType, array $data): ?string
    {
        return match($eventType) {
            'checkout.session.completed' => $data['id'] ?? null,
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted' => $data['id'] ?? null,
            'invoice.payment_succeeded',
            'invoice.payment_failed' => $data['id'] ?? null,
            default => $data['id'] ?? null,
        };
    }

    /**
     * Tenta extrair o user_id do evento.
     */
    protected function extractUserId(string $eventType, array $data): ?int
    {
        // Para checkout.session.completed, usamos client_reference_id
        if ($eventType === 'checkout.session.completed') {
            $refId = $data['client_reference_id'] ?? null;
            return $refId ? (int) $refId : null;
        }

        // Para outros eventos, tentamos encontrar pelo stripe_id do customer
        $customerId = $data['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            return $user?->id;
        }

        return null;
    }
}
