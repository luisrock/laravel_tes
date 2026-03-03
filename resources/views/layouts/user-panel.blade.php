@extends('layouts.app')

@push('styles')
    @yield('panel-styles')
@endpush

@section('content')
<div class="tw-mx-auto tw-max-w-4xl">
    <div class="tw-mb-8 tw-flex tw-justify-between tw-items-center">
        <h1 class="tw-text-2xl tw-font-bold tw-text-slate-900">@yield('panel-title', 'Minha Conta')</h1>
        <a href="{{ route('searchpage') }}" class="tw-inline-flex tw-items-center tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">
            <svg class="tw-mr-2 tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar ao site
        </a>
    </div>

    @yield('panel-content')
</div>
@endsection

@push('scripts')
    @yield('panel-scripts')
@endpush
