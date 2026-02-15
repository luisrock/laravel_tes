@extends('layouts.app')

@section('content')
<div class="tw-mx-auto tw-max-w-md tw-w-full">
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-text-center">{{ __('Verificar seu e-mail') }}</h2>
        </div>

        <div class="tw-p-6">
            @if (session('resent'))
                <div class="tw-mb-4 tw-rounded-md tw-bg-green-50 tw-p-4">
                    <div class="tw-flex">
                        <div class="tw-flex-shrink-0">
                            <i class="fas fa-check-circle tw-h-5 tw-w-5 tw-text-green-400"></i>
                        </div>
                        <div class="tw-ml-3">
                            <p class="tw-text-sm tw-font-medium tw-text-green-800">
                                {{ __('Um novo link de verificação foi enviado para seu e-mail.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="tw-text-sm tw-text-slate-600 tw-space-y-4">
                <p>
                    {{ __('Antes de continuar, verifique seu e-mail e clique no link de verificação.') }}
                </p>
                <p>
                    {{ __('Se você não recebeu o e-mail') }},
                </p>
                
                <form class="tw-inline" method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    @honeypot
                    <button type="submit" class="tw-text-brand-600 hover:tw-text-brand-700 tw-font-medium hover:tw-underline focus:tw-outline-none">
                        {{ __('clique aqui para reenviar') }}
                    </button>.
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
