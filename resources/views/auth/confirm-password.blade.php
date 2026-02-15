@extends('layouts.app')

@section('content')
<div class="tw-mx-auto tw-max-w-md tw-w-full">
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-text-center">{{ __('Confirmar senha') }}</h2>
        </div>

        <div class="tw-p-6">
            <p class="tw-mb-6 tw-text-sm tw-text-slate-600 tw-text-center">
                {{ __('Por favor, confirme sua senha antes de continuar.') }}
            </p>

            <form method="POST" action="{{ route('password.confirm.store') }}" class="tw-space-y-6">
                @csrf

                <div>
                    <label for="password" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Senha') }}</label>
                    <div class="tw-mt-1">
                        <input id="password" type="password" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('password') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="password" required autocomplete="current-password">
                    </div>
                    @error('password')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-flex tw-items-center tw-justify-between">
                    @if (Route::has('password.request'))
                        <div class="tw-text-sm">
                            <a href="{{ route('password.request') }}" class="tw-font-medium tw-text-brand-600 hover:tw-text-brand-500">
                                {{ __('Esqueceu a senha?') }}
                            </a>
                        </div>
                    @endif
                </div>

                <div>
                    <button type="submit" class="tw-flex tw-w-full tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                        {{ __('Confirmar') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
