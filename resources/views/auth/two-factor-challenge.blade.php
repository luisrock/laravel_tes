@extends('layouts.app')

@section('content')
<div class="tw-mx-auto tw-max-w-md tw-w-full" x-data="{ recovery: false }">
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-text-center">{{ __('Autenticação em dois fatores') }}</h2>
        </div>

        <div class="tw-p-6">
            <p class="tw-mb-6 tw-text-sm tw-text-slate-600 tw-text-center" x-show="!recovery">
                {{ __('Por favor, confirme o acesso à sua conta informando o código de autenticação fornecido pelo seu aplicativo autenticador.') }}
            </p>

            <p class="tw-mb-6 tw-text-sm tw-text-slate-600 tw-text-center" x-show="recovery" x-cloak>
                {{ __('Por favor, confirme o acesso à sua conta informando um dos seus códigos de recuperação de emergência.') }}
            </p>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="tw-space-y-6">
                @csrf

                <div x-show="!recovery">
                    <label for="code" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Código') }}</label>
                    <div class="tw-mt-1">
                        <input id="code" type="text" inputmode="numeric" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('code') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="code" autocomplete="one-time-code" autofocus x-ref="code">
                    </div>
                    @error('code')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="recovery" x-cloak>
                    <label for="recovery_code" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Código de recuperação') }}</label>
                    <div class="tw-mt-1">
                        <input id="recovery_code" type="text" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('recovery_code') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="recovery_code" autocomplete="one-time-code" x-ref="recovery_code">
                    </div>
                    @error('recovery_code')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-flex tw-items-center tw-justify-end">
                    <button type="button" class="tw-text-sm tw-text-brand-600 hover:tw-text-brand-500 tw-font-medium hover:tw-underline focus:tw-outline-none"
                            x-show="!recovery"
                            x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                        {{ __('Usar um código de recuperação') }}
                    </button>

                    <button type="button" class="tw-text-sm tw-text-brand-600 hover:tw-text-brand-500 tw-font-medium hover:tw-underline focus:tw-outline-none"
                            x-show="recovery"
                            x-cloak
                            x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                        {{ __('Usar um código de autenticação') }}
                    </button>
                </div>

                <div>
                    <button type="submit" class="tw-flex tw-w-full tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                        {{ __('Login') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
