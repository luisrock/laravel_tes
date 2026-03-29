@extends('layouts.user-panel')

@section('panel-title', 'Editar Coleção')

@section('panel-content')
    <div class="tw-mb-4">
        <a href="{{ route('colecoes.index') }}" class="tw-inline-flex tw-items-center tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline text-decoration-none">
            <svg class="tw-mr-1.5 tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Minhas coleções
        </a>
    </div>

    <livewire:collection-edit :collection-id="$collection->id" />
@endsection
