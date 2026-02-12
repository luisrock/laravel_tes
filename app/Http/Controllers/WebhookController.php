<?php

namespace App\Http\Controllers;

use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\WelcomeSubscriberNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Laravel\Cashier\Subscription;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CashierWebhookController
{
    /**
     * Handle incoming webhook.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        $eventId = $payload['id'] ?? null;
        $eventType = $payload['type'] ?? null;
        $data = $payload['data']['object'] ?? [];

        if (! $eventId || ! $eventType) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $stripeObjectId = $data['id'] ?? null;
        $userId = $this->extractUserId($eventType, $data);

        $webhookEvent = StripeWebhookEvent::firstOrCreate(
            ['stripe_event_id' => $eventId],
            [
                'event_type' => $eventType,
                'stripe_object_id' => $stripeObjectId,
                'user_id' => $userId,
                'received_at' => now(),
            ]
        );

        if ($webhookEvent->isProcessed()) {
            return response()->json(['status' => 'already_processed']);
        }

        if (! $webhookEvent->wasRecentlyCreated) {
            $webhookEvent->increment('attempts');
        }

        try {
            $response = parent::handleWebhook($request);

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
    protected function handleCheckoutSessionCompleted(array $payload): Response
    {
        $session = $payload['data']['object'];
        $clientReferenceId = $session['client_reference_id'] ?? null;

        if ($clientReferenceId) {
            StripeWebhookEvent::where('stripe_object_id', $session['id'])
                ->whereNull('user_id')
                ->update(['user_id' => $clientReferenceId]);

            $user = User::find($clientReferenceId);
            if ($user) {
                try {
                    $user->notify(new WelcomeSubscriberNotification);
                } catch (Exception $e) {
                    Log::error('Erro ao enviar email de boas-vindas', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle customer.subscription.created event.
     */
    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $subscription = $payload['data']['object'];
        $this->updateCurrentPeriodEnd($subscription);

        return parent::handleCustomerSubscriptionCreated($payload);
    }

    /**
     * Handle customer.subscription.updated event.
     */
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
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
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        $invoice = $payload['data']['object'];
        $stripeSubscriptionId = $invoice['subscription'] ?? null;

        if ($stripeSubscriptionId) {
            try {
                $stripeSubscription = Cashier::stripe()->subscriptions->retrieve($stripeSubscriptionId);
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

        if (! $stripeId || ! $currentPeriodEnd) {
            return;
        }

        Subscription::where('stripe_id', $stripeId)->update([
            'current_period_end' => Carbon::createFromTimestamp($currentPeriodEnd),
        ]);
    }

    /**
     * Tenta extrair o user_id do evento.
     */
    protected function extractUserId(string $eventType, array $data): ?int
    {
        if ($eventType === 'checkout.session.completed') {
            $refId = $data['client_reference_id'] ?? null;

            return $refId ? (int) $refId : null;
        }

        $customerId = $data['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();

            return $user?->id;
        }

        return null;
    }
}
