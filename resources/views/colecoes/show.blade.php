@extends('front.base')

@section('page-title', $collection->title . ' — Coleção de ' . $owner->name)

@section('styles')
<meta property="og:title" content="{{ $collection->title }} — Coleção de {{ $owner->name }} - Teses & Súmulas">
<meta property="og:description" content="{{ $collection->description ?? 'Coleção de teses e súmulas organizada por ' . $owner->name . ' no Teses & Súmulas.' }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ route('colecoes.show', [$owner->name, $collection->slug]) }}">
@endsection

@section('content')

    {{-- Hero --}}
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-border tw-border-slate-200">

            {{-- Breadcrumb mínimo --}}
            <p class="tw-text-xs tw-text-slate-400 tw-mb-3">
                Coleção de <span class="tw-font-medium tw-text-slate-600">{{ $owner->name }}</span>
            </p>

            <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-start sm:tw-justify-between tw-gap-4">
                <div class="tw-space-y-2">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-brand-100 tw-text-brand-800">Coleção</span>
                        @if ($collection->is_private)
                            <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600">
                                <i class="fa fa-lock tw-mr-0.5"></i> Privada
                            </span>
                        @endif
                    </div>
                    <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">{{ $collection->title }}</h1>
                    @if ($collection->description)
                        <p class="tw-text-slate-500 tw-text-base tw-leading-relaxed tw-m-0">{{ $collection->description }}</p>
                    @endif
                    <p class="tw-text-slate-400 tw-text-sm tw-m-0">
                        {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
                    </p>
                </div>

                {{-- Botões de ação --}}
                <div class="tw-flex tw-flex-wrap tw-gap-2 tw-shrink-0" x-data>
                    {{-- Compartilhar no WhatsApp --}}
                    <a
                        href="https://wa.me/?text={{ urlencode($collection->title . ' — ' . route('colecoes.show', [$owner->name, $collection->slug])) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-green-700 hover:tw-border-green-300 tw-transition-colors text-decoration-none"
                    >
                        <i class="fa fa-whatsapp tw-text-green-600"></i>
                        WhatsApp
                    </a>

                    {{-- Copiar link --}}
                    <button
                        type="button"
                        x-data="{ copied: false }"
                        x-on:click="navigator.clipboard.writeText('{{ route('colecoes.show', [$owner->name, $collection->slug]) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-brand-600 hover:tw-border-brand-300 tw-transition-colors"
                    >
                        <template x-if="!copied">
                            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </template>
                        <template x-if="copied">
                            <svg class="tw-w-4 tw-h-4 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <span x-text="copied ? 'Copiado!' : 'Copiar link'"></span>
                    </button>

                    {{-- Editar (só para o dono logado) --}}
                    @auth
                        @if (auth()->id() === $collection->user_id)
                            <a
                                href="{{ route('colecoes.edit', $collection->id) }}"
                                class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-rounded-lg tw-border tw-border-brand-300 tw-bg-brand-50 tw-text-sm tw-font-medium tw-text-brand-700 hover:tw-bg-brand-100 tw-transition-colors text-decoration-none"
                            >
                                <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar coleção
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

        </section>
    </div>
    {{-- END Hero --}}

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10 tw-mt-6 tw-space-y-4">

        {{-- Lista de itens --}}
        @if ($items->isNotEmpty())
            <div class="tw-space-y-4">
                @foreach ($items as $item)
                    <x-collection-item-card :item="$item" />
                @endforeach
            </div>

        @else
            {{-- Estado vazio --}}
            <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-p-10 tw-text-center">
                <svg class="tw-w-10 tw-h-10 tw-text-slate-300 tw-mx-auto tw-mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <p class="tw-text-slate-500 tw-text-sm tw-m-0">Esta coleção ainda não tem itens.</p>
            </div>
        @endif

        {{-- CTA para guests --}}
        @guest
            <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-brand-200 tw-p-6 tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-gap-4">
                <div class="tw-flex-1">
                    <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-mb-1">Organize suas pesquisas</h3>
                    <p class="tw-text-slate-500 tw-text-sm tw-m-0">Crie sua conta grátis e salve teses e súmulas em coleções personalizadas.</p>
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
