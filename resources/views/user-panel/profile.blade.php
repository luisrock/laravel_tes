@extends('layouts.user-panel')

@section('panel-title', 'Perfil')

@section('panel-content')

@if(session('status') === 'profile-information-updated')
    <div class="tw-bg-green-50 tw-border-l-4 tw-border-green-400 tw-p-4 tw-mb-6 tw-rounded-r-md">
        <div class="tw-flex">
            <div class="tw-flex-shrink-0">
                <svg class="tw-h-5 tw-w-5 tw-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="tw-ml-3">
                <p class="tw-text-sm tw-text-green-700">
                    Perfil atualizado com sucesso.
                </p>
            </div>
        </div>
    </div>
@endif

@if(session('status') === 'password-updated')
    <div class="tw-bg-green-50 tw-border-l-4 tw-border-green-400 tw-p-4 tw-mb-6 tw-rounded-r-md">
        <div class="tw-flex">
            <div class="tw-flex-shrink-0">
                <svg class="tw-h-5 tw-w-5 tw-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="tw-ml-3">
                <p class="tw-text-sm tw-text-green-700">
                    Senha atualizada com sucesso.
                </p>
            </div>
        </div>
    </div>
@endif

<div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-mb-6" id="profile-info">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
        <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Informações do perfil</h3>
    </div>
    <div class="tw-p-6">
        <form method="POST" action="{{ url('/user/profile-information') }}" class="tw-space-y-6">
            @csrf
            @method('PUT')
            <div class="tw-max-w-xl">
                <label for="name" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">Nome</label>
                <div class="tw-mt-1">
                    <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required autofocus autocomplete="name" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm">
                </div>
            </div>
            
            <div class="tw-max-w-xl">
                <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">E-mail</label>
                <div class="tw-mt-1">
                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="username" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm">
                </div>
                @error('email')
                    <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="tw-pt-2">
                <button type="submit" class="tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-mb-6">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
        <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Atualizar senha</h3>
    </div>
    <div class="tw-p-6">
        <form method="POST" action="{{ url('/user/password') }}" class="tw-space-y-6">
            @csrf
            @method('PUT')
            <div class="tw-max-w-xl">
                <label for="current_password" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">Senha atual</label>
                <div class="tw-mt-1">
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm">
                </div>
            </div>

            <div class="tw-max-w-xl">
                <label for="password" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">Nova senha</label>
                <div class="tw-mt-1">
                    <input type="password" id="password" name="password" required autocomplete="new-password" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm">
                </div>
            </div>

            <div class="tw-max-w-xl">
                <label for="password_confirmation" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">Confirmar nova senha</label>
                <div class="tw-mt-1">
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm">
                </div>
            </div>

            <div class="tw-pt-2">
                <button type="submit" class="tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                    Atualizar senha
                </button>
            </div>
        </form>
    </div>
</div>

<div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-mb-6" id="2fa">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
        <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Autenticação em dois fatores</h3>
    </div>
    <div class="tw-p-6">
        @if(auth()->user()->two_factor_confirmed_at)
            <p class="tw-text-sm tw-text-slate-600 tw-mb-4">2FA está ativo. Para desativar, confirme sua senha e clique em Desativar.</p>
            <div class="tw-flex tw-items-center tw-gap-4">
                <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="tw-inline" onsubmit="return confirm('Tem certeza que deseja desativar a autenticação em dois fatores?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-red-300 tw-bg-white tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-red-700 tw-shadow-sm hover:tw-bg-red-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-500 focus:tw-ring-offset-2 tw-transition-colors">
                        Desativar 2FA
                    </button>
                </form>
                <a href="{{ url('/user/two-factor-recovery-codes') }}" class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">Ver códigos de recuperação</a>
            </div>
        @else
            <p class="tw-text-sm tw-text-slate-600 tw-mb-4">Adicione segurança adicional à sua conta usando autenticação em dois fatores.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                <button type="submit" class="tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                    Ativar 2FA
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
