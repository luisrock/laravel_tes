<?php

namespace App\Http\Controllers;

use App\Models\RefundRequest;
use App\Notifications\RefundRequestReceivedNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RefundRequestController extends Controller
{
    /**
     * Formulário de solicitação de estorno.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        // Verificar se usuário tem assinatura
        if (! $subscription) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Você não possui uma assinatura ativa.');
        }

        // Verificar se já existe solicitação pendente
        $pendingRequest = RefundRequest::where('user_id', $user->id)
            ->pending()
            ->first();

        if ($pendingRequest) {
            return redirect()->route('subscription.show')
                ->with('info', 'Você já possui uma solicitação de estorno em análise.');
        }

        return view('subscription.refund', [
            'user' => $user,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Salva solicitação de estorno.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:20|max:1000',
        ], [
            'reason.required' => 'Por favor, descreva o motivo da solicitação.',
            'reason.min' => 'A descrição deve ter pelo menos 20 caracteres.',
            'reason.max' => 'A descrição não pode exceder 1000 caracteres.',
        ]);

        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        if (! $subscription) {
            return back()->with('error', 'Você não possui uma assinatura ativa.');
        }

        // Verificar se já existe solicitação pendente
        $pendingRequest = RefundRequest::where('user_id', $user->id)
            ->pending()
            ->first();

        if ($pendingRequest) {
            return redirect()->route('subscription.show')
                ->with('info', 'Você já possui uma solicitação de estorno em análise.');
        }

        // Buscar última invoice paga
        $invoiceId = null;
        $paymentIntentId = null;

        try {
            $invoices = $user->invoices();
            $lastPaidInvoice = $invoices->first(function ($invoice) {
                return $invoice->paid;
            });

            if ($lastPaidInvoice) {
                $invoiceId = $lastPaidInvoice->id;
                $paymentIntentId = $lastPaidInvoice->payment_intent;
            }
        } catch (Exception $e) {
            Log::warning('Não foi possível buscar invoices para refund request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Criar solicitação
        $refundRequest = RefundRequest::create([
            'user_id' => $user->id,
            'cashier_subscription_id' => $subscription->id,
            'stripe_subscription_id' => $subscription->stripe_id,
            'stripe_invoice_id' => $invoiceId,
            'stripe_payment_intent_id' => $paymentIntentId,
            'reason' => $request->input('reason'),
            'status' => RefundRequest::STATUS_PENDING,
        ]);

        Log::info('Nova solicitação de estorno criada', [
            'refund_request_id' => $refundRequest->id,
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
        ]);

        try {
            $user->notify(new RefundRequestReceivedNotification($refundRequest));
        } catch (Exception $e) {
            Log::warning('Não foi possível enviar notificação de estorno', [
                'refund_request_id' => $refundRequest->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('subscription.show')
            ->with('success', 'Sua solicitação de estorno foi enviada. Analisaremos em até 48 horas úteis.');
    }
}
