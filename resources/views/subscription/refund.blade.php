@extends('layouts.user-panel')

@section('panel-title', 'Solicitar Estorno')

@section('panel-content')
<div class="tw-max-w-3xl">
    
    <div class="tw-bg-yellow-50 tw-border-l-4 tw-border-yellow-400 tw-p-4 tw-mb-6">
        <div class="tw-flex">
            <div class="tw-flex-shrink-0">
                <svg class="tw-h-5 tw-w-5 tw-text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="tw-ml-3">
                <p class="tw-text-sm tw-text-yellow-700">
                    <strong>Importante:</strong> Antes de solicitar estorno, considere cancelar sua assinatura. Você manterá acesso até o fim do período pago. O estorno é irreversível e encerrará seu acesso imediatamente.
                </p>
            </div>
        </div>
    </div>
    
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Detalhes da solicitação</h3>
        </div>
        
        <div class="tw-p-6">
            <div class="tw-bg-slate-50 tw-rounded-md tw-p-4 tw-mb-6 tw-border tw-border-slate-200">
                <h4 class="tw-text-sm tw-font-semibold tw-text-slate-900 tw-mb-2">Sua Assinatura</h4>
                <div class="tw-text-sm tw-text-slate-600">
                    <p><span class="tw-font-medium tw-text-slate-700">Plano:</span> {{ $subscription->stripe_id ?? 'N/A' }}</p>
                    <p><span class="tw-font-medium tw-text-slate-700">Desde:</span> {{ $subscription->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
            
            <form action="{{ route('refund.store') }}" method="POST">
                @csrf
                
                <div class="tw-mb-6">
                    <label for="reason" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Motivo da solicitação</label>
                    <textarea 
                        name="reason" 
                        id="reason" 
                        rows="4"
                        class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm"
                        placeholder="Por favor, descreva o motivo da sua solicitação de estorno. Isso nos ajuda a melhorar nossos serviços."
                        required
                        minlength="20"
                        maxlength="1000"
                    >{{ old('reason') }}</textarea>
                    <p class="tw-mt-1 tw-text-xs tw-text-slate-500">Mínimo de 20 caracteres</p>
                    @error('reason')
                        <p class="tw-mt-1 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
                    <button type="submit" class="tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                        Enviar Solicitação
                    </button>
                    <a href="{{ route('subscription.show') }}" class="tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-slate-300 tw-bg-white tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-slate-700 tw-shadow-sm hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors text-decoration-none">
                        Cancelar
                    </a>
                </div>
            </form>
            
            <p class="tw-mt-6 tw-text-sm tw-text-slate-500">
                Sua solicitação será analisada pela nossa equipe em até 48 horas úteis. Você receberá uma resposta por e-mail.
            </p>
        </div>
    </div>
</div>
@endsection
