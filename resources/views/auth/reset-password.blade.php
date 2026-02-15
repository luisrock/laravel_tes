@extends('layouts.app')

@section('content')
<div class="tw-mx-auto tw-max-w-md tw-w-full">
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-text-center">{{ __('Redefinir senha') }}</h2>
        </div>

        <div class="tw-p-6">
            <form method="POST" action="{{ route('password.update') }}" class="tw-space-y-6">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('E-Mail') }}</label>
                    <div class="tw-mt-1">
                        <input id="email" type="email" class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('email') tw-border-red-300 tw-text-red-900 focus:tw-border-red-500 focus:tw-ring-red-500 @enderror" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" autofocus>
                    </div>
                    @error('email')
                        <p class="tw-mt-2 tw-text-sm tw-text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700">{{ __('Nova senha') }}</label>
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

                <div>
                    <button type="submit" class="tw-flex tw-w-full tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-bg-brand-600 tw-py-2 tw-px-4 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition-colors">
                        {{ __('Redefinir senha') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
