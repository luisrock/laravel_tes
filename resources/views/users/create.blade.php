@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <div class="tw-max-w-2xl tw-mx-auto">
        <!-- Header -->
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">{{ __('Add User') }}</h1>
            <a href="{{ route('users.index') }}" class="tw-inline-flex tw-items-center tw-text-slate-600 hover:tw-text-brand-600 tw-transition-colors">
                <i class="fas fa-arrow-left tw-mr-2"></i> Voltar
            </a>
        </div>

        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 md:tw-p-8">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="tw-mb-6">
                    <label for="name" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">{{ __('Name') }} <span class="tw-text-rose-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus 
                           class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('name') tw-border-rose-500 @enderror">
                    @error('name')
                        <p class="tw-text-xs tw-text-rose-500 tw-mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-mb-6">
                    <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">{{ __('Email') }} <span class="tw-text-rose-500">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required 
                           class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('email') tw-border-rose-500 @enderror">
                    @error('email')
                        <p class="tw-text-xs tw-text-rose-500 tw-mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-mb-8">
                    <label for="role" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">{{ __('Role') }}</label>
                    <select id="role" name="role" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('role') tw-border-rose-500 @enderror">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="tw-text-xs tw-text-rose-500 tw-mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-flex tw-justify-end tw-gap-4">
                    <a href="{{ route('users.index') }}" class="tw-px-6 tw-py-2.5 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="tw-px-6 tw-py-2.5 tw-bg-brand-600 tw-text-white tw-font-bold tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                        <i class="fas fa-save tw-mr-2"></i> {{ __('Add User') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection