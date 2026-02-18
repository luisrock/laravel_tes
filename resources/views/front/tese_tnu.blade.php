@extends('front.base')

@section('page-title', $label)

@section('content')

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
                <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">Representativo de Controvérsia</span>
                <span class="tw-text-slate-500 tw-text-sm">{{ $tribunal }}</span>
            </div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">
                Tema {{ $tese->numero }}
                @if(!empty($tese->isCancelada) && $tese->isCancelada)
                    <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-red-100 tw-text-red-800 tw-align-middle"><i class="fa fa-ban"></i> CANCELADO</span>
                @endif
            </h1>

            <div class="tw-flex tw-flex-wrap tw-gap-2 tw-items-center">
                @if(!empty($tese->ramo))
                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-purple-100 tw-text-purple-800 tw-border tw-border-purple-200">
                        {{ $tese->ramo }}
                    </span>
                @endif
                @if(!empty($tese->situacao))
                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-slate-100 tw-text-slate-700 tw-border tw-border-slate-200">
                        Situação: {{ $tese->situacao }}
                    </span>
                @endif
            </div>
        </section>
    </div>

    @if(isset($breadcrumb))
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4 tw-pb-2">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10" id="content-results">
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">

            <div class="lg:tw-col-span-2 tw-space-y-6">
                {{-- Tese Fixada --}}
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-flex tw-justify-between tw-items-center">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Tese Fixada @if(!empty($tese->isCancelada) && $tese->isCancelada)<span class="tw-text-red-600 tw-font-normal">(cancelada)</span>@endif</h3>
                        <button class="btn-copy-text tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors" data-clipboard-text="{{ $tese->to_be_copied }}">
                            <i class="fa fa-copy tw-mr-1.5"></i> <span class="btn-text">Copiar</span>
                        </button>
                    </div>
                    <div class="tw-p-6 md:tw-p-8">
                        <div class="tw-prose tw-prose-slate tw-max-w-none">
                            <p class="tw-text-xl tw-font-serif tw-text-slate-800 tw-leading-relaxed">
                                {{ $tese->tese_texto ?: '[aguarda julgamento]' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Questão Submetida --}}
                @if(!empty($tese->questao))
                    <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                        <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Questão Submetida a Julgamento</h3>
                        </div>
                        <div class="tw-p-6">
                            <p class="tw-text-slate-700 tw-leading-relaxed">
                                {{ $tese->questao }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="tw-space-y-6">
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-p-5">
                        <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4">Informações do Julgamento</h4>

                        <dl class="tw-space-y-4">
                            @if(!empty($tese->relator))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Relator</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->relator }}</dd>
                            </div>
                            @endif

                            @if(!empty($tese->processo))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Processo</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->processo }}</dd>
                            </div>
                            @endif

                            @if(!empty($tese->julgadoEm))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Julgado em</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->julgadoEm }}</dd>
                            </div>
                            @endif

                            @if(!empty($tese->publicadoEm))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Publicado em</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->publicadoEm }}</dd>
                            </div>
                            @endif

                            @if(!empty($tese->transito))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Trânsito em Julgado</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->transito }}</dd>
                            </div>
                            @endif
                        </dl>

                        <div class="tw-mt-6 tw-pt-6 tw-border-t tw-border-slate-100 tw-space-y-3">
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($tese->to_be_copied . ' ' . Request::url()) }}"
                               target="_blank"
                               class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-bg-green-600 tw-text-white hover:tw-bg-green-700 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                                <i class="fab fa-whatsapp tw-mr-2"></i> Compartilhar no Zap
                            </a>

                            @if(!empty($tese->link))
                            <a href="{{ $tese->link }}" target="_blank" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-text-slate-700 hover:tw-bg-slate-50 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                                Ver origem <i class="fa fa-external-link-alt tw-ml-2 tw-text-xs"></i>
                            </a>
                            @endif

                            <a href="{{ route('tnualltesespage') }}" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-text-slate-700 hover:tw-bg-slate-50 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                                Voltar à lista
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
