@extends('front.base')

@section('page-title', 'Resultados da Pesquisa')

@section('content')

<div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-py-8 md:tw-py-12 tw-space-y-8">

    <!-- Search Section -->
    @include('partials.search-form', ['keyword' => $keyword ?? '', 'tribunal' => $tribunal ?? '', 'lista_tribunais' => $lista_tribunais])
    <!-- END Search Section -->

    <!-- Content Results -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        
        <!-- Tabs -->
        <div class="tw-flex tw-border-b tw-border-slate-200 tw-bg-slate-50" role="tablist">
            <button type="button" 
                class="home-results-tab tw-flex-1 tw-py-4 tw-px-6 tw-text-sm md:tw-text-base tw-font-medium tw-text-slate-600 hover:tw-text-brand-700 hover:tw-bg-slate-100 tw-transition-colors focus:tw-outline-none is-active" 
                data-tab-target="#busca-sumulas-trib" 
                id="nav-sumulas" 
                aria-selected="true">
                Súmulas
            </button>
            
            @hasSection('teses_total_text')
            <button type="button" 
                class="home-results-tab tw-flex-1 tw-py-4 tw-px-6 tw-text-sm md:tw-text-base tw-font-medium tw-text-slate-600 hover:tw-text-brand-700 hover:tw-bg-slate-100 tw-transition-colors focus:tw-outline-none" 
                data-tab-target="#busca-teses-trib" 
                id="nav-teses" 
                aria-selected="false">
                Teses
            </button>
            @endif
        </div>

        <div class="tw-p-6 md:tw-p-8">

            {{-- PDF Button Logic (Hidden/Restricted) --}}
            @php
                $isAdmin = auth()->check() && in_array(auth()->user()->email, config('tes_constants.admins', []));
            @endphp
            @if ($isAdmin && (!empty($output['sumula']['total']) || !empty($output['tese']['total'])) && (empty($_GET['print']) || 'pdf' != $_GET['print']))
                <div id="pdf-button" class="tw-flex tw-justify-end tw-mb-4" style="{{ $display_pdf ?? '' }}">
                    <a href="{{ url()->full() }}&print=pdf"
                        class="tw-inline-flex tw-items-center tw-justify-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-red-600 hover:tw-bg-red-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-red-500 tw-transition-colors"
                        target="_blank" 
                        rel="nofollow">
                        <i class="fa fa-file-pdf-o tw-mr-2"></i> Gerar PDF
                    </a>
                </div>
            @endif

            <!-- Súmulas Pane -->
            <section class="home-results-pane is-active tw-space-y-6" id="busca-sumulas-trib" role="tabpanel">
                <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-text-slate-700">
                    @yield('sumulas_total_text')
                </div>
                
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-border-separate tw-border-spacing-y-4">
                        <tbody class="tw-space-y-4">
                            @yield('sumulas_inner_table')
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Teses Pane -->
            <section class="home-results-pane tw-hidden tw-space-y-6" id="busca-teses-trib" role="tabpanel">
                <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-text-slate-700">
                    @yield('teses_total_text')
                </div>

                <div class="tw-overflow-x-auto">
                     <table class="tw-w-full tw-border-separate tw-border-spacing-y-4">
                        <tbody class="tw-space-y-4">
                            @yield('teses_inner_table')
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>
</div>

<style>
    /* Tab Active State */
    .home-results-tab.is-active {
        @apply tw-bg-white tw-border-b-2 tw-border-b-brand-600 tw-text-brand-800 tw-font-semibold;
        margin-bottom: -1px; /* Overlap border */
    }
    
    /* Pane Active State */
    .home-results-pane.tw-hidden {
        display: none;
    }
</style>

<script>
(function () {
    const tabs = document.querySelectorAll('.home-results-tab');
    const panes = document.querySelectorAll('.home-results-pane');

    if (!tabs.length || !panes.length) {
        return;
    }

    function activateTab(targetSelector) {
        tabs.forEach(function (tab) {
            const isActive = tab.dataset.tabTarget === targetSelector;
            if (isActive) {
                tab.classList.add('is-active');
                tab.setAttribute('aria-selected', 'true');
            } else {
                tab.classList.remove('is-active');
                tab.setAttribute('aria-selected', 'false');
            }
        });

        panes.forEach(function (pane) {
            const isActive = `#${pane.id}` === targetSelector;
            if (isActive) {
               pane.classList.remove('tw-hidden');
            } else {
               pane.classList.add('tw-hidden');
            }
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activateTab(tab.dataset.tabTarget);
        });
    });
})();
</script>

@endsection
