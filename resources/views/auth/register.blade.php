@extends('layouts.app')

@section('content')
<div class="tw-mx-auto tw-max-w-md tw-w-full">
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-text-center">{{ __('Crie sua conta') }}</h2>
        </div>

        <div class="tw-p-6">
            <form method="POST" action="{{ route('register') }}" class="tw-space-y-6">
                @csrf

                <div>
                    <label for="name" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Nome') }}</label>
                    <div class="tw-mt-1">
                        <input id="name" type="text" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('name') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    </div>
                    @error('name')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('E-Mail') }}</label>
                    <div class="tw-mt-1">
                        <input id="email" type="email" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('email') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                    </div>
                    @error('email')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Senha') }}</label>
                    <div class="tw-mt-1">
                        <input id="password" type="password" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('password') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="password" required autocomplete="new-password">
                    </div>
                    @error('password')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password-confirm" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Confirmar senha') }}</label>
                    <div class="tw-mt-1">
                        <input id="password-confirm" type="password" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm" name="password_confirmation" required autocomplete="new-password">
                    </div>
                </div>

                @honeypot

                <div>
                    <button type="submit" class="tw-flex tw-w-full tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                        {{ __('Cadastrar-se') }}
                    </button>
                </div>
                
                @if (Route::has('login'))
                    <div class="tw-mt-4 tw-text-center tw-text-sm tw-text-slate-600">
                        Já tem uma conta? 
                        <a href="{{ route('login') }}" class="tw-font-medium tw-text-brand-600 hover:tw-text-brand-500">
                            Faça login
                        </a>
                    </div>
                @endif
            </form>
            
            {{-- Google Login temporariamente desativado
            <div class="tw-mt-6">
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-0 tw-flex tw-items-center">
                        <div class="tw-w-full tw-border-t tw-border-gray-300"></div>
                    </div>
                    <div class="tw-relative tw-flex tw-justify-center tw-text-sm">
                        <span class="tw-bg-white tw-px-2 tw-text-gray-500">Ou continue com</span>
                    </div>
                </div>

                <div class="tw-mt-6 tw-grid tw-grid-cols-1 tw-gap-3">
                    <a href="{{ url('auth/google') }}" class="tw-inline-flex tw-w-full tw-justify-center tw-rounded-md tw-border tw-border-gray-300 tw-bg-white tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-gray-500 tw-shadow-sm hover:tw-bg-gray-50 text-decoration-none">
                        <span class="tw-sr-only">Entrar com Google</span>
                        <svg class="tw-h-5 tw-w-5" aria-hidden="true" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .533 5.333.533 12S5.867 24 12.48 24c3.48 0 6.16-1.147 8.213-3.28 2.08-2.08 2.667-5.187 2.667-7.68 0-.52-.053-1.04-.133-1.52h-10.747z" />
                        </svg>
                        <span class="tw-ml-2">Google</span>
                    </a>
                </div>
            </div>
            --}}
        </div>
    </div>
</div>
@endsection
