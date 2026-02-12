<?php

namespace App\Http\Controllers;

use App\Enums\RefundRequestStatus;
use App\Models\RefundRequest;
use App\Notifications\RefundRequestReceivedNotification;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RefundRequestController extends Controller
{
    /**
     * Formulario de solicitacao de estorno.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        if (! $subscription) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Voce nao possui uma assinatura ativa.');
        }

        $pendingRequest = RefundRequest::where('user_id', $user->id)
            ->pending()
            ->first();

        if ($pendingRequest) {
            return redirect()->route('subscription.show')
                ->with('info', 'Voce ja possui uma solicitacao de estorno em analise.');
        }

        return view('subscription.refund', [
            'user' => $user,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Salva solicitacao de estorno.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:20|max:1000',
        ], [
            'reason.required' => 'Por favor, descreva o motivo da solicitacao.',
            'reason.min' => 'A descricao deve ter pelo menos 20 caracteres.',
            'reason.max' => 'A descricao nao pode exceder 1000 caracteres.',
        ]);

        $user = $request->user();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        $subscription = $user->subscription($subscriptionName);

        if (! $subscription) {
            return back()->with('error', 'Voce nao possui uma assinatura ativa.');
        }

        $pendingRequest = RefundRequest::where('user_id', $user->id)
            ->pending()
            ->first();

        if ($pendingRequest) {
            return redirect()->route('subscription.show')
                ->with('info', 'Voce ja possui uma solicitacao de estorno em analise.');
        }

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
            Log::warning('Nao foi possivel buscar invoices para refund request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $refundRequest = RefundRequest::create([
            'user_id' => $user->id,
            'cashier_subscription_id' => $subscription->id,
            'stripe_subscription_id' => $subscription->stripe_id,
            'stripe_invoice_id' => $invoiceId,
            'stripe_payment_intent_id' => $paymentIntentId,
            'reason' => $request->input('reason'),
            'status' => RefundRequestStatus::Pending,
        ]);

        Log::info('Nova solicitacao de estorno criada', [
            'refund_request_id' => $refundRequest->id,
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
        ]);

        try {
            $user->notify(new RefundRequestReceivedNotification($refundRequest));
        } catch (Exception $e) {
            Log::warning('Nao foi possivel enviar notificacao de estorno', [
                'refund_request_id' => $refundRequest->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('subscription.show')
            ->with('success', 'Sua solicitacao de estorno foi enviada. Analisaremos em ate 48 horas uteis.');
    }
}
