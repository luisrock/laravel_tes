@extends('front.base')

@section('page-title', $label)

@section('content')

    <!-- Page Content -->
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
                 <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">Tema (Pesquisa Pronta)</span>
            </div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">{{ $label }}</h1>
            <p class="tw-text-slate-600 tw-mt-2">
                Faça <a href="{{ route('searchpage') }}" class="tw-text-brand-700 hover:tw-text-brand-800 hover:tw-underline">outra pesquisa</a> ou veja as
                <a href="{{ route('alltemaspage') }}" class="tw-text-brand-700 hover:tw-text-brand-800 hover:tw-underline">pesquisas prontas</a>.
                @if($admin)
                    <span class="tw-mx-2 text-slate-300">|</span>
                    <a href="{{ route('admin') }}" class="tw-text-brand-700 hover:tw-text-brand-800 hover:tw-underline">Admin</a>
                @endif
            </p>
        </section>
    </div>
    <!-- END Hero -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10 tw-mt-6" id="content-results">

        <!-- CONCEPT SECTION -->
        @if($concept)
        <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden tw-mb-8" x-data="{ expanded: false }">
            <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-flex tw-justify-between tw-items-center">
                <!-- 2. Renamed to Resumo -->
                <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Resumo</h3>
                @if($admin)
                    <div class="tw-flex tw-items-center tw-gap-2">
                         @if(!$concept_validated)
                            <span class="tw-text-xs tw-font-bold tw-text-amber-600 tw-uppercase tw-bg-amber-100 tw-px-2 tw-py-1 tw-rounded">Pendente</span>
                         @endif
                    </div>
                @endif
            </div>
            <div class="tw-p-6 md:tw-p-8">
                 <!-- 2. Line limiter with expand button -->
                 <div class="tw-prose tw-prose-slate tw-max-w-none tw-relative">
                    <div :class="expanded ? '' : 'tw-line-clamp-4 tw-max-h-[8rem] tw-overflow-hidden'" class="tw-transition-all tw-duration-300">
                        {!! $concept !!}
                    </div>
                    
                    <button @click="expanded = !expanded" 
                            x-show="!expanded"
                            class="tw-mt-2 tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline focus:tw-outline-none">
                        Ler mais
                    </button>
                    <button @click="expanded = !expanded" 
                            x-show="expanded"
                            style="display: none;"
                            class="tw-mt-2 tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline focus:tw-outline-none">
                        Ler menos
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- TABS / SECTIONS -->
        @if(count($output) > 0)
            @php
                // Logic to find the first tab with results
                $firstActiveTab = null;
                $validTabs = []; // Keep track of tabs with results
                foreach($output as $key => $data) {
                     if($key !== 'total_count') {
                         $count = $data['total_count'] ?? 0;
                         if ($count > 0) {
                             if (is_null($firstActiveTab)) {
                                 $firstActiveTab = $key;
                             }
                             $validTabs[] = $key;
                         }
                     }
                }
                // Fallback: if no tab has results, default to the first one (usually STF)
                if (is_null($firstActiveTab)) {
                    foreach($output as $key => $data) {
                        if($key !== 'total_count') {
                            $firstActiveTab = $key;
                            break;
                        }
                    }
                }
            @endphp

            <div x-data="{ activeTab: '{{ $firstActiveTab }}' }" class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                
                <!-- Tab Headers -->
                <div class="tw-flex tw-overflow-x-auto tw-border-b tw-border-slate-200 tw-bg-slate-50">
                    @foreach($output as $key => $data)
                        @if($key !== 'total_count')
                            @php 
                                $count = $data['total_count'] ?? 0; 
                                $isDisabled = $count === 0;
                            @endphp
                            <button 
                                @if(!$isDisabled) @click="activeTab = '{{ $key }}'" @endif
                                :class="{
                                    'tw-border-brand-500 tw-text-brand-600 tw-bg-white': activeTab === '{{ $key }}',
                                    'tw-border-transparent tw-text-slate-500 hover:tw-text-slate-700 hover:tw-border-slate-300': activeTab !== '{{ $key }}' && !{{ $isDisabled ? 'true' : 'false' }},
                                    'tw-opacity-25 tw-cursor-not-allowed': {{ $isDisabled ? 'true' : 'false' }}
                                }"
                                class="tw-whitespace-nowrap tw-py-4 tw-px-6 tw-border-b-2 tw-font-medium tw-text-sm tw-transition-colors focus:tw-outline-none"
                                {{ $isDisabled ? 'disabled' : '' }}>
                                {{ strtoupper($key) }}
                            </button>
                        @endif
                    @endforeach
                </div>

                <!-- Tab Contents -->
                <div class="tw-p-6">
                    @foreach($output as $key => $data)
                        @if($key !== 'total_count')
                            <div x-show="activeTab === '{{ $key }}'" style="display: none;">
                                <div class="tw-mb-4 tw-text-sm tw-text-slate-500">
                                    <code class="tw-font-bold tw-text-slate-700">{{ $label }}</code> - {{ strtoupper($key) }}
                                    (resultados: <code class="tw-font-bold tw-text-slate-700">{{ $data['total_count'] ?? 0 }}</code>)
                                </div>
                                
                                <div class="tw-overflow-x-auto">
                                    <!-- 1. Fix spacing by forcing block display on table elements via CSS class override logic or utility wrapper -->
                                    <table class="tw-w-full tw-border-collapse result-table-spacer">
                                        <tbody class="tw-block tw-space-y-4">
                                            @includeIf('front.results.inners.'.$key.'_sum', ['output' => $data])
                                            @includeIf('front.results.inners.'.$key.'_rep', ['output' => $data])
                                        </tbody>
                                    </table>
                                </div>

                                @if(($data['total_count'] ?? 0) == 0)
                                    <div class="tw-text-center tw-py-8 tw-text-slate-500">
                                        Nenhum resultado encontrado para {{ strtoupper($key) }}.
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Temas Relacionados -->
        @if(isset($related_themes) && $related_themes->count() > 0)
        <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden tw-mt-8">
            <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200">
                <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">
                    <i class="fa fa-project-diagram tw-mr-2 tw-text-slate-400"></i> Assuntos Relacionados
                </h3>
            </div>
            <div class="tw-p-6">
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    @foreach($related_themes as $rt)
                    <a href="{{ route('temapage', ['tema' => $rt->slug]) }}" 
                       class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-brand-50 hover:tw-border-brand-200 hover:tw-text-brand-700 tw-transition-colors">
                        {{ $rt->label ?? $rt->keyword }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <style>
        /* Force table rows to behave like blocks to support spacing */
        .result-table-spacer tbody tr {
            display: block;
            margin-bottom: 1rem;
        }
        .result-table-spacer tbody tr:last-child {
            margin-bottom: 0;
        }
    </style>

@endsection
