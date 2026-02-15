@extends('layouts.user-panel')

@section('panel-title', 'Visão Geral')

@section('panel-content')
<div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-mb-6">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
        <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Status da conta</h3>
    </div>
    
    <div class="tw-divide-y tw-divide-slate-100">
        <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-slate-700 tw-font-medium">E-mail verificado</span>
            <span class="tw-flex tw-items-center tw-gap-2">
                @if(auth()->user()->hasVerifiedEmail())
                    <span class="tw-text-green-600 tw-font-semibold tw-flex tw-items-center">
                        <svg class="tw-w-4 tw-h-4 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Sim
                    </span>
                @else
                    <span class="tw-text-orange-500 tw-font-semibold tw-flex tw-items-center">
                        <svg class="tw-w-4 tw-h-4 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Pendente
                    </span>
                    <form method="POST" action="{{ route('verification.send') }}" class="tw-inline">
                        @csrf
                        <button type="submit" class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline tw-ml-2">Reenviar verificação</button>
                    </form>
                @endif
            </span>
        </div>

        <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-slate-700 tw-font-medium">Autenticação em dois fatores</span>
            <span class="tw-flex tw-items-center tw-gap-2">
                @if(auth()->user()->two_factor_confirmed_at)
                    <span class="tw-text-green-600 tw-font-semibold tw-flex tw-items-center">
                        <svg class="tw-w-4 tw-h-4 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Ativo
                    </span>
                @else
                    <span class="tw-text-slate-500">Não configurado</span>
                    <a href="{{ route('user-panel.profile') }}#2fa" class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline tw-ml-2">Configurar</a>
                @endif
            </span>
        </div>

        <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between">
            <span class="tw-text-slate-700 tw-font-medium">Assinatura</span>
            <span class="tw-flex tw-items-center tw-gap-2">
            @if(auth()->user()->isSubscriber())
                @if(auth()->user()->isOnGracePeriod())
                    <span class="tw-text-orange-500 tw-font-semibold">Em carência</span>
                @else
                    <span class="tw-text-green-600 tw-font-semibold tw-flex tw-items-center">
                        <svg class="tw-w-4 tw-h-4 tw-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Ativa
                    </span>
                @endif
            @else
                <span class="tw-text-slate-500">Sem assinatura</span>
            @endif
            </span>
        </div>
    </div>
</div>

<div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
        <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Ações rápidas</h3>
    </div>
    <div class="tw-p-6 tw-flex tw-flex-wrap tw-gap-3">
        <a href="{{ route('user-panel.profile') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-shadow-sm tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
            Editar perfil
        </a>
        @if(auth()->user()->isSubscriber())
            <a href="{{ route('subscription.portal') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-shadow-sm tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
                Gerenciar assinatura
            </a>
            <a href="{{ route('refund.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-slate-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
                Solicitar estorno
            </a>
        @else
            <a href="{{ route('subscription.plans') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-shadow-sm tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
                Ver planos
            </a>
        @endif
    </div>
</div>
@endsection
