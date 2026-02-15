@extends('layouts.app')

@section('page-title', 'Checkout Cancelado')

@section('content')
<div class="tw-max-w-7xl tw-mx-auto tw-py-16 tw-px-4 sm:tw-px-6 lg:tw-px-8">
    <div class="tw-max-w-2xl tw-mx-auto tw-bg-white tw-shadow-lg tw-rounded-lg tw-overflow-hidden tw-border tw-border-slate-200">
        <div class="tw-px-6 tw-py-10 sm:tw-px-10 tw-text-center">
            <div class="tw-mx-auto tw-h-20 tw-w-20 tw-bg-slate-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-8">
                <svg class="tw-h-12 tw-w-12 tw-text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            
            <h1 class="tw-text-3xl tw-font-extrabold tw-text-slate-900 tw-mb-4">Checkout Cancelado</h1>
            <p class="tw-text-lg tw-text-slate-600 tw-mb-10">
                Você cancelou o processo de checkout.<br>
                Nenhuma cobrança foi realizada.
            </p>
            
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-center tw-gap-4">
                <a href="{{ route('subscription.plans') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
                    Ver planos novamente
                </a>
                <a href="{{ route('searchpage') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-border tw-border-slate-300 tw-text-base tw-font-medium tw-rounded-md tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
                    Voltar ao site
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
