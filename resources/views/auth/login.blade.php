@extends('layouts.app')

@section('content')
<div class="tw-mx-auto tw-max-w-md tw-w-full">
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-text-center">{{ __('Acesse sua conta') }}</h2>
        </div>

        <div class="tw-p-6">
            <div class="tw-grid tw-grid-cols-1 tw-gap-3">
                <a href="{{ route('auth.google') }}" class="tw-inline-flex tw-w-full tw-justify-center tw-items-center tw-rounded-md tw-border tw-border-gray-300 tw-bg-white tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-gray-700 tw-shadow-sm hover:tw-bg-gray-50 text-decoration-none">
                    <svg class="tw-h-5 tw-w-5 tw-mr-2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Entrar com Google
                </a>
            </div>

            <div class="tw-mt-6 tw-mb-6">
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-0 tw-flex tw-items-center">
                        <div class="tw-w-full tw-border-t tw-border-gray-300"></div>
                    </div>
                    <div class="tw-relative tw-flex tw-justify-center tw-text-sm">
                        <span class="tw-bg-white tw-px-2 tw-text-gray-500">Ou continue com email</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="tw-space-y-6">
                @csrf
                
                @if(request()->has('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif

                <div>
                    <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('E-Mail') }}</label>
                    <div class="tw-mt-1">
                        <input id="email" type="email" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('email') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    </div>
                    @error('email')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Senha') }}</label>
                    <div class="tw-mt-1">
                        <input id="password" type="password" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('password') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="password" required autocomplete="current-password">
                    </div>
                    @error('password')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @honeypot

                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-flex tw-items-center">
                        <input id="remember" name="remember" type="checkbox" class="tw-h-4 tw-w-4 tw-rounded tw-border-slate-300 tw-text-brand-600 focus:tw-ring-brand-500" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="tw-ml-2 tw-block tw-text-sm tw-text-slate-700">
                            {{ __('Lembrar de mim') }}
                        </label>
                    </div>

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
                        {{ __('Entrar') }}
                    </button>
                </div>
                
                @if (Route::has('register'))
                    <div class="tw-mt-4 tw-text-center tw-text-sm tw-text-slate-600">
                        Não tem uma conta? 
                        <a href="{{ route('register') }}" class="tw-font-medium tw-text-brand-600 hover:tw-text-brand-500">
                            Cadastre-se
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
