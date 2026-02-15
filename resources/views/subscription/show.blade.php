@extends('layouts.user-panel')

@section('panel-title', 'Minha Assinatura')

@section('panel-content')

@if(session('success'))
    <div class="tw-bg-green-50 tw-border-l-4 tw-border-green-400 tw-p-4 tw-mb-6 tw-rounded-r-md">
        <div class="tw-flex">
            <div class="tw-flex-shrink-0">
                <svg class="tw-h-5 tw-w-5 tw-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="tw-ml-3">
                <p class="tw-text-sm tw-text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('info'))
    <div class="tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-4 tw-mb-6 tw-rounded-r-md">
        <div class="tw-flex">
            <div class="tw-flex-shrink-0">
                <svg class="tw-h-5 tw-w-5 tw-text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="tw-ml-3">
                <p class="tw-text-sm tw-text-blue-700">{{ session('info') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="tw-space-y-6">
    @if($isSubscriber)
        @if($isOnGracePeriod)
        <div class="tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-md tw-p-4 tw-text-yellow-800">
            <div class="tw-flex">
                <div class="tw-flex-shrink-0">
                    <svg class="tw-h-5 tw-w-5 tw-text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="tw-ml-3">
                    <h3 class="tw-text-sm tw-font-medium tw-text-yellow-800">Assinatura cancelada</h3>
                    <div class="tw-mt-2 tw-text-sm tw-text-yellow-700">
                        <p>Você ainda tem acesso até {{ $accessEndsAt->format('d/m/Y') }}. Após essa data, sua assinatura será encerrada.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
                <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Detalhes da assinatura</h3>
            </div>
            
            <div class="tw-divide-y tw-divide-slate-100">
                <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
                    <span class="tw-text-slate-500 tw-font-medium">Status</span>
                    @if($isOnGracePeriod)
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">
                            Em período de carência
                        </span>
                    @else
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                            Ativa
                        </span>
                    @endif
                </div>
                
                @if($planName)
                <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
                    <span class="tw-text-slate-500 tw-font-medium">Plano</span>
                    <span class="tw-text-slate-900 tw-font-semibold tw-uppercase">{{ $planName }}</span>
                </div>
                @endif
                
                @if($subscription)
                <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
                    <span class="tw-text-slate-500 tw-font-medium">Assinante desde</span>
                    <span class="tw-text-slate-900 tw-font-semibold">{{ $subscription->created_at->format('d/m/Y') }}</span>
                </div>
                
                @if($subscription->current_period_end && !$isOnGracePeriod)
                <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
                    <span class="tw-text-slate-500 tw-font-medium">Próxima renovação</span>
                    <span class="tw-text-slate-900 tw-font-semibold">{{ \Carbon\Carbon::parse($subscription->current_period_end)->format('d/m/Y') }}</span>
                </div>
                @endif
                @endif
            </div>

            <div class="tw-px-6 tw-py-6 tw-bg-slate-50 tw-rounded-b-lg">
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-4">
                    <a href="{{ route('subscription.portal') }}" class="tw-inline-flex tw-justify-center tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-shadow-sm tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors">
                        Gerenciar Assinatura
                    </a>
                </div>
                <p class="tw-mt-3 tw-text-sm tw-text-slate-500">
                    No portal, você pode atualizar seu cartão, trocar de plano ou cancelar.
                </p>
                <div class="tw-mt-4">
                    <a href="{{ route('refund.create') }}" class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">Solicitar estorno</a>
                </div>
            </div>
        </div>
        
        <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
                <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Seus Benefícios</h3>
            </div>
            <div class="tw-p-6">
                <ul class="tw-space-y-3">
                    @if($user->hasFeature('no_ads'))
                    <li class="tw-flex tw-items-start">
                        <svg class="tw-flex-shrink-0 tw-h-5 tw-w-5 tw-text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="tw-ml-3 tw-text-slate-700">Navegação sem anúncios</span>
                    </li>
                    @endif
                    @if($user->hasFeature('exclusive_content'))
                    <li class="tw-flex tw-items-start">
                        <svg class="tw-flex-shrink-0 tw-h-5 tw-w-5 tw-text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="tw-ml-3 tw-text-slate-700">Acesso a conteúdo exclusivo</span>
                    </li>
                    @endif
                    @if($user->hasFeature('ai_tools'))
                    <li class="tw-flex tw-items-start">
                        <svg class="tw-flex-shrink-0 tw-h-5 tw-w-5 tw-text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="tw-ml-3 tw-text-slate-700">Ferramentas de IA</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    @else
        <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-py-12 tw-px-6 tw-text-center">
            <h2 class="tw-text-xl tw-font-semibold tw-text-slate-900 tw-mb-3">Você não possui uma assinatura ativa</h2>
            <p class="tw-text-slate-500 tw-mb-8">Assine agora para navegar sem anúncios e acessar conteúdo exclusivo.</p>
            <a href="{{ route('subscription.plans') }}" class="tw-inline-flex tw-justify-center tw-items-center tw-px-5 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
                Ver Planos
            </a>
        </div>
    @endif
</div>
@endsection
