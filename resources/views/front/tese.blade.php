@extends('front.base')

@section('page-title', $label)

@section('content')

    <!-- Page Content -->

    <!-- Page Content -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
                 <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">Tese Vinculante</span>
                 <span class="tw-text-slate-500 tw-text-sm">{{ $tribunal }}</span>
            </div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">Tema {{ $tese->numero }}</h1>
            
            <div class="tw-flex tw-flex-wrap tw-gap-2 tw-items-center">
                 @if(isset($tese->isCancelada) && $tese->isCancelada)
                    <div class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-red-100 tw-text-red-800">
                        <i class="fa fa-ban"></i> CANCELADO
                    </div>
                @endif
                @if(isset($tese->situacao))
                     <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-slate-100 tw-text-slate-700 tw-border tw-border-slate-200">
                         Situação: {{ $tese->situacao }}
                     </span>
                @endif
            </div>

        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4 tw-pb-2">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10" id="content-results">

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
            
            <!-- Main Content -->
            <div class="lg:tw-col-span-2 tw-space-y-6">
                <!-- Tese Box -->
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-flex tw-justify-between tw-items-center">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Tese Fixada</h3>
                        <button class="btn-copy-text tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors" data-clipboard-text="{{ $tese->to_be_copied }}">
                            <i class="fa fa-copy tw-mr-1.5"></i> <span class="btn-text">Copiar</span>
                        </button>
                    </div>
                    <div class="tw-p-6 md:tw-p-8">
                         <div class="tw-prose tw-prose-slate tw-max-w-none">
                            <p class="tw-text-xl tw-font-serif tw-text-slate-800 tw-leading-relaxed">
                                {{ $tese->tese_texto }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Questão Box -->
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Questão Submetida a Julgamento</h3>
                    </div>
                    <div class="tw-p-6">
                         <p class="tw-text-slate-700 tw-leading-relaxed">
                            {{ $tese->tema_texto }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="tw-space-y-6">
                 <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-p-5">
                        <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4">Informações do Julgamento</h4>
                        
                        <dl class="tw-space-y-4">
                            @if(isset($tese->relator))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Relator</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->relator }}</dd>
                            </div>
                            @endif

                            @if(isset($tese->acordao))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Acórdão (Leading Case)</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->acordao }}</dd>
                            </div>
                            @endif

                            @if(isset($tese->tempo))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Data</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->tempo }}</dd>
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
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
