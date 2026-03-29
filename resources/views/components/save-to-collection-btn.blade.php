@props(['type', 'tribunal', 'contentId'])

@auth
    <button
        type="button"
        onclick="window.dispatchEvent(new CustomEvent('open-save-modal', { detail: { type: '{{ $type }}', tribunal: '{{ $tribunal }}', contentId: {{ (int) $contentId }} } }))"
        class="tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-1 focus:tw-ring-brand-500 tw-transition-colors"
        title="Salvar em coleção"
    >
        <svg class="tw-w-4 tw-h-4 tw-mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
        </svg>
        Salvar
    </button>
@else
    <a
        href="{{ route('login') }}"
        class="tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600 tw-transition-colors text-decoration-none"
        title="Faça login para salvar em coleção"
    >
        <svg class="tw-w-4 tw-h-4 tw-mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
        </svg>
        Salvar
    </a>
@endauth
