@extends('front.base')

@section('page-title', 'Extensão para Chrome')

@section('content')
<main id="main-container">
    {{-- Hero --}}
    <section class="tw-bg-gradient-to-b tw-from-brand-700 tw-to-brand-800 tw-text-white">
        <div class="tw-max-w-5xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-16 sm:tw-py-20">
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-12 tw-items-center">
                <div>
                    <span class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-full tw-bg-white/15 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide">
                        <i class="fa fa-chrome" aria-hidden="true"></i> Extensão gratuita
                    </span>
                    <h1 class="tw-mt-4 tw-text-3xl sm:tw-text-4xl tw-font-bold tw-leading-tight">
                        Jurisprudência dos tribunais, a um clique do seu navegador
                    </h1>
                    <p class="tw-mt-4 tw-text-base sm:tw-text-lg tw-text-white/90">
                        Pesquise súmulas, teses e enunciados dos tribunais mais importantes sem sair da aba em que você está.
                        A extensão do Teses &amp; Súmulas mostra as contagens por tribunal e leva você direto ao teor no site.
                    </p>
                    <div class="tw-mt-8 tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
                        <a href="{{ extension_webstore_url('extensao_page') }}" target="_blank" rel="noopener"
                           class="tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-rounded-lg tw-bg-white tw-px-6 tw-py-3 tw-text-base tw-font-semibold tw-text-brand-700 tw-shadow-sm hover:tw-bg-slate-100 tw-transition">
                            <i class="fa fa-chrome" aria-hidden="true"></i> Instalar no Chrome
                        </a>
                        <a href="{{ route('searchpage') }}"
                           class="tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-rounded-lg tw-border tw-border-white/40 tw-px-6 tw-py-3 tw-text-base tw-font-semibold tw-text-white hover:tw-bg-white/10 tw-transition">
                            Pesquisar no site
                        </a>
                    </div>
                    <p class="tw-mt-4 tw-text-sm tw-text-white/70">
                        Compatível com Google Chrome · Sem cadastro · 100% gratuito
                    </p>
                </div>

                {{-- Mock do popup da extensão (placeholder visual até termos um print/GIF real) --}}
                <div class="tw-justify-self-center" aria-hidden="true">
                    <div class="tw-w-[320px] tw-rounded-xl tw-bg-white tw-text-slate-900 tw-shadow-2xl tw-ring-1 tw-ring-black/5 tw-overflow-hidden">
                        <div class="tw-flex tw-items-center tw-gap-2 tw-px-4 tw-py-3 tw-border-b tw-border-slate-100">
                            <span class="tw-inline-flex tw-h-7 tw-w-7 tw-items-center tw-justify-center tw-rounded-md tw-bg-brand-700 tw-text-white tw-text-[11px] tw-font-bold">T&amp;S</span>
                            <span class="tw-text-sm tw-font-semibold">Teses &amp; Súmulas</span>
                        </div>
                        <div class="tw-p-4">
                            <div class="tw-flex tw-items-center tw-gap-2 tw-rounded-lg tw-border tw-border-slate-200 tw-px-3 tw-py-2">
                                <i class="fa fa-search tw-text-slate-400" aria-hidden="true"></i>
                                <span class="tw-text-sm tw-text-slate-500">dano moral</span>
                            </div>
                            <div class="tw-mt-3 tw-space-y-2">
                                @foreach ([['STF', 12], ['STJ', 34], ['TST', 8], ['TNU', 5]] as [$sigla, $qtd])
                                    <div class="tw-flex tw-items-center tw-justify-between tw-rounded-lg tw-bg-slate-50 tw-px-3 tw-py-2">
                                        <span class="tw-text-sm tw-font-medium tw-text-slate-700">{{ $sigla }}</span>
                                        <span class="tw-text-xs tw-font-semibold tw-text-brand-700">{{ $qtd }} resultados</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Benefícios --}}
    <section class="tw-bg-white">
        <div class="tw-max-w-5xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-14">
            <h2 class="tw-text-2xl tw-font-bold tw-text-slate-900 tw-text-center">Por que instalar</h2>
            <div class="tw-mt-10 tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
                @foreach ([
                    ['fa-bolt', 'Busca rápida', 'Consulte súmulas e teses sem abrir uma nova aba nem perder o contexto do que está lendo.'],
                    ['fa-building-o', 'Tudo num só lugar', 'STF, STJ, TST, TNU, CARF, FONAJE e CEJ reunidos numa única busca.'],
                    ['fa-gift', 'Gratuito', 'Sem assinatura e sem cadastro para pesquisar. É só instalar e usar.'],
                    ['fa-external-link', 'Direto ao site', 'Um clique leva você ao teor completo e às análises no Teses & Súmulas.'],
                ] as [$icone, $titulo, $texto])
                    <div class="tw-rounded-xl tw-border tw-border-slate-200 tw-p-6 tw-text-center hover:tw-shadow-md tw-transition">
                        <span class="tw-inline-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-brand-50 tw-text-brand-700">
                            <i class="fa {{ $icone }} tw-text-lg" aria-hidden="true"></i>
                        </span>
                        <h3 class="tw-mt-4 tw-text-base tw-font-semibold tw-text-slate-900">{{ $titulo }}</h3>
                        <p class="tw-mt-2 tw-text-sm tw-text-slate-600">{{ $texto }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="tw-bg-slate-50 tw-border-t tw-border-slate-200">
        <div class="tw-max-w-3xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-14 tw-text-center">
            <h2 class="tw-text-2xl tw-font-bold tw-text-slate-900">Comece agora</h2>
            <p class="tw-mt-3 tw-text-slate-600">
                Instale a extensão e tenha a jurisprudência dos principais tribunais sempre à mão.
            </p>
            <div class="tw-mt-6">
                <a href="{{ extension_webstore_url('extensao_page') }}" target="_blank" rel="noopener"
                   class="tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-rounded-lg tw-bg-brand-700 tw-px-6 tw-py-3 tw-text-base tw-font-semibold tw-text-white tw-shadow-sm hover:tw-bg-brand-800 tw-transition">
                    <i class="fa fa-chrome" aria-hidden="true"></i> Instalar no Chrome
                </a>
            </div>
        </div>
    </section>
</main>
@endsection
