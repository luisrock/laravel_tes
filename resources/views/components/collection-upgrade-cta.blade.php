@props([
    'title' => 'Recurso exclusivo para assinantes',
    'description' => 'Faça upgrade do seu plano para desbloquear este recurso.',
    'compact' => false,
])

@if ($compact)
    <div class="tw-flex tw-items-start tw-gap-3 tw-p-3 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded-lg">
        <svg class="tw-w-4 tw-h-4 tw-text-amber-500 tw-shrink-0 tw-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <div class="tw-min-w-0">
            <p class="tw-text-sm tw-font-medium tw-text-amber-800">{{ $title }}</p>
            @if (config('subscription.enabled'))
                <p class="tw-text-xs tw-text-amber-700 tw-mt-0.5">{{ $description }}</p>
                <a href="{{ route('subscription.plans') }}" class="tw-mt-1.5 tw-inline-flex tw-text-xs tw-font-semibold tw-text-amber-700 hover:tw-text-amber-900 hover:tw-underline text-decoration-none">
                    Ver planos →
                </a>
            @endif
        </div>
    </div>
@else
    <div class="tw-flex tw-items-start tw-gap-4 tw-p-5 tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded-lg">
        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-bg-amber-100 tw-flex tw-items-center tw-justify-center">
            <svg class="tw-w-5 tw-h-5 tw-text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <div class="tw-flex-1 tw-min-w-0">
            <p class="tw-text-sm tw-font-semibold tw-text-amber-900">{{ $title }}</p>
            @if (config('subscription.enabled'))
                <p class="tw-text-sm tw-text-amber-800 tw-mt-1">{{ $description }}</p>
                <a
                    href="{{ route('subscription.plans') }}"
                    class="tw-mt-3 tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-amber-500 hover:tw-bg-amber-600 tw-transition-colors text-decoration-none"
                >
                    Ver planos
                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
@endif
