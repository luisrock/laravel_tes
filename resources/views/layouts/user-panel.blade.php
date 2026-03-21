@extends('layouts.app')

@push('styles')
    @yield('panel-styles')
@endpush

@section('content')
<div class="tw-mx-auto tw-max-w-5xl">
    <div class="tw-mb-8 tw-flex tw-justify-between tw-items-center">
        <h1 class="tw-text-2xl tw-font-bold tw-text-slate-900">@yield('panel-title', 'Minha Conta')</h1>
        <a href="{{ route('searchpage') }}" class="tw-inline-flex tw-items-center tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">
            <svg class="tw-mr-2 tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar ao site
        </a>
    </div>

    <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-6">
        {{-- Navegação lateral --}}
        <nav class="tw-w-full md:tw-w-56 tw-shrink-0">
            <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-overflow-hidden">
                <a href="{{ route('user-panel.dashboard') }}"
                   class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-border-b tw-border-slate-100 text-decoration-none {{ request()->routeIs('user-panel.dashboard') ? 'tw-bg-brand-50 tw-text-brand-700' : 'tw-text-slate-700 hover:tw-bg-slate-50' }}">
                    <svg class="tw-w-5 tw-h-5 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" /></svg>
                    Visão Geral
                </a>
                <a href="{{ route('user-panel.history') }}"
                   class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-border-b tw-border-slate-100 text-decoration-none {{ request()->routeIs('user-panel.history') ? 'tw-bg-brand-50 tw-text-brand-700' : 'tw-text-slate-700 hover:tw-bg-slate-50' }}">
                    <svg class="tw-w-5 tw-h-5 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Histórico
                </a>
                <span class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-slate-400 tw-border-b tw-border-slate-100 tw-cursor-default">
                    <svg class="tw-w-5 tw-h-5 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                    Coleções
                    <span class="tw-text-xs tw-bg-slate-100 tw-text-slate-500 tw-px-1.5 tw-py-0.5 tw-rounded-full">em breve</span>
                </span>
                <a href="{{ route('user-panel.profile') }}"
                   class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-border-b tw-border-slate-100 text-decoration-none {{ request()->routeIs('user-panel.profile') ? 'tw-bg-brand-50 tw-text-brand-700' : 'tw-text-slate-700 hover:tw-bg-slate-50' }}">
                    <svg class="tw-w-5 tw-h-5 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Perfil
                </a>
                @if(config('subscription.enabled'))
                <a href="{{ route('subscription.show') }}"
                   class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-text-sm tw-font-medium text-decoration-none {{ request()->routeIs('subscription.show') ? 'tw-bg-brand-50 tw-text-brand-700' : 'tw-text-slate-700 hover:tw-bg-slate-50' }}">
                    <svg class="tw-w-5 tw-h-5 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    Assinatura
                </a>
                @endif
            </div>
        </nav>

        {{-- Conteúdo principal --}}
        <div class="tw-flex-1 tw-min-w-0">
            @yield('panel-content')
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @yield('panel-scripts')
@endpush
