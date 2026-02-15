@extends('layouts.app')

@section('content')
<div class="tw-mx-auto tw-max-w-md tw-w-full">
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-text-center">{{ __('Recuperar senha') }}</h2>
        </div>

        <div class="tw-p-6">
            @if (session('status'))
                <div class="tw-mb-4 tw-rounded-md tw-bg-green-50 tw-p-4">
                    <div class="tw-flex">
                        <div class="tw-flex-shrink-0">
                            <i class="fas fa-check-circle tw-h-5 tw-w-5 tw-text-green-400"></i>
                        </div>
                        <div class="tw-ml-3">
                            <p class="tw-text-sm tw-font-medium tw-text-green-800">
                                {{ session('status') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <p class="tw-mb-6 tw-text-sm tw-text-slate-600 tw-text-center">
                {{ __('Esqueceu sua senha? Sem problemas. Informe seu endereço de e-mail e enviaremos um link para você redefinir sua senha.') }}
            </p>

            <form method="POST" action="{{ route('password.email') }}" class="tw-space-y-6">
                @csrf

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
                    <button type="submit" class="tw-flex tw-w-full tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                        {{ __('Enviar link de redefinição') }}
                    </button>
                </div>
            </form>
            
            <div class="tw-mt-6 tw-text-center">
                <a href="{{ route('login') }}" class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-500">
                    <i class="fas fa-arrow-left tw-mr-1"></i> Voltar para o login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
