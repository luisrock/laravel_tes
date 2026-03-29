@extends('front.base')

@section('page-title', 'Coleções — Teses & Súmulas')

@section('content')

    {{-- Hero --}}
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-2 tw-border tw-border-slate-200">
            <div class="tw-flex tw-items-center tw-gap-2 tw-mb-1">
                <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-brand-100 tw-text-brand-800">Coleções</span>
            </div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">Coleções da comunidade</h1>
            <p class="tw-text-slate-500 tw-text-base tw-m-0">
                Teses e súmulas organizadas por usuários do Teses & Súmulas.
            </p>
        </section>
    </div>
    {{-- END Hero --}}

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10 tw-mt-6">

        @if ($collections->isEmpty())
            <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-p-12 tw-text-center">
                <svg class="tw-w-10 tw-h-10 tw-text-slate-300 tw-mx-auto tw-mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <p class="tw-text-slate-500 tw-text-sm tw-m-0">Nenhuma coleção pública ainda.</p>
            </div>
        @else
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
                @foreach ($collections as $col)
                    <a
                        href="{{ route('colecoes.show', [$col->user->name, $col->slug]) }}"
                        class="tw-group tw-bg-white tw-rounded-xl tw-border tw-border-slate-200 tw-p-5 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all tw-flex tw-flex-col tw-gap-3 text-decoration-none"
                    >
                        <div class="tw-flex tw-items-start tw-justify-between tw-gap-2">
                            <h2 class="tw-text-sm tw-font-semibold tw-text-slate-800 group-hover:tw-text-brand-700 tw-transition-colors tw-leading-snug tw-m-0 tw-flex-1">
                                {{ $col->title }}
                            </h2>
                            <span class="tw-shrink-0 tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-text-slate-400 tw-font-medium tw-mt-0.5">
                                <svg class="tw-w-3.5 tw-h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                {{ $col->items_count }}
                            </span>
                        </div>

                        @if ($col->description)
                            <p class="tw-text-xs tw-text-slate-500 tw-leading-relaxed tw-m-0 tw-line-clamp-2">
                                {{ Str::limit($col->description, 100) }}
                            </p>
                        @endif

                        <div class="tw-mt-auto tw-flex tw-items-center tw-justify-between tw-pt-2 tw-border-t tw-border-slate-100">
                            <span class="tw-text-xs tw-text-slate-400">
                                por <span class="tw-font-medium tw-text-slate-600">{{ $col->user->name }}</span>
                            </span>
                            <span class="tw-text-xs tw-text-brand-600 group-hover:tw-text-brand-800 tw-font-medium tw-transition-colors">
                                Ver →
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($collections->hasPages())
                <div class="tw-mt-8">
                    {{ $collections->links() }}
                </div>
            @endif
        @endif

        @guest
            <div class="tw-mt-6 tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-brand-200 tw-p-6 tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-gap-4">
                <div class="tw-flex-1">
                    <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-mb-1">Crie sua própria coleção</h3>
                    <p class="tw-text-slate-500 tw-text-sm tw-m-0">Registre-se gratuitamente e organize teses e súmulas em coleções personalizadas.</p>
                </div>
                <a
                    href="{{ route('register') }}"
                    class="tw-inline-flex tw-items-center tw-gap-2 tw-px-5 tw-py-2.5 tw-rounded-lg tw-bg-brand-600 tw-text-white tw-text-sm tw-font-semibold hover:tw-bg-brand-700 tw-transition-colors tw-shrink-0 text-decoration-none"
                >
                    Criar conta grátis
                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        @endguest

    </div>

@endsection
