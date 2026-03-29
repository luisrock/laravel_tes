@extends('front.base')

@section('page-title', $label)

@section('content')

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">{{ $label }}</h1>
            <p class="tw-text-slate-600 tw-text-lg tw-leading-relaxed tw-m-0">
                Faça uma <a href="{{ route('searchpage') }}" class="tw-text-brand-600 hover:tw-text-brand-800 tw-font-medium hover:tw-underline">pesquisa</a> ou veja as
                <a href="{{ route('alltemaspage') }}" class="tw-text-brand-600 hover:tw-text-brand-800 tw-font-medium hover:tw-underline">pesquisas prontas</a>.
                @if ($admin)
                    <br><a href="{{ route('admin') }}" class="tw-text-sm tw-text-slate-400 hover:tw-text-slate-600">Admin</a>
                @endif
            </p>
        </section>
    </div>

    @if(isset($breadcrumb))
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4 tw-pb-2">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10" id="content-results">

        <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
            <div class="tw-p-6 md:tw-p-8">
                <div id="search-container" class="tw-hidden tw-mb-6 tw-p-4 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-xl">
                    <div class="tw-flex tw-items-stretch tw-gap-3">
                        <div class="tw-relative tw-flex-grow">
                            <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                <i class="fa fa-search tw-text-slate-400"></i>
                            </div>
                            <input type="text" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2.5 tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-900 placeholder:tw-text-slate-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500 sm:tw-text-sm"
                                id="table-search-input"
                                placeholder="Pesquisar por tema, número ou texto da tese...">
                        </div>
                        <button class="tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-4 tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-slate-800 tw-transition-colors" type="button" id="clear-search-btn" style="display:none;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div class="tw-flex tw-items-center tw-justify-between tw-mt-2">
                        <small class="tw-text-slate-500">Digite para filtrar instantaneamente.</small>
                        <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer tw-select-none tw-group">
                            <input type="checkbox" id="search-number-only" class="tw-h-4 tw-w-4 tw-rounded tw-border-slate-300 tw-accent-brand-600 tw-cursor-pointer">
                            <span class="tw-text-xs tw-text-slate-500 group-has-[:checked]:tw-text-brand-600 tw-transition-colors">
                                Número do Tema
                            </span>
                        </label>
                    </div>
                </div>

                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-gap-4 tw-mb-6 tw-pb-4 tw-border-b tw-border-slate-100">
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">Teses</span>
                        <span class="tw-text-slate-600 tw-font-medium">{{ $tribunal }}</span>
                        <span class="tw-text-slate-400 text-sm">(<span id="results-count">{{ $count }}</span> resultados)</span>
                    </div>
                    <button class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-brand-600 tw-transition-colors" id="toggle-search-btn">
                        <i class="fa fa-search"></i> Buscar na lista
                    </button>
                </div>

                <div class="tw-space-y-4" id="teses-list">
                    @php
                        $ai_teses = function_exists('get_teses_with_ai') ? get_teses_with_ai('TST') : [];
                    @endphp
                    @foreach ($teses as $tes)
                        @php
                            $has_ai = in_array($tes->id ?? null, $ai_teses);
                        @endphp
                        <div class="tese-item tw-block tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-6 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all" data-numero="{{ $tes->numero }}">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0 tw-flex tw-items-center tw-gap-3 tw-flex-wrap">
                                    <a href="{{ route('tsttesepage', ['tese' => $tes->numero]) }}" class="tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline tw-underline-offset-2">
                                        TEMA {{ $tes->numero }}
                                    </a>
                                    @if($has_ai)
                                        <x-ia-badge size="sm" :url="route('tsttesepage', ['tese' => $tes->numero])" />
                                    @endif
                                </h4>
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600">
                                    Precedentes Vinculantes
                                </span>
                            </div>

                            <div class="tw-prose tw-prose-slate tw-max-w-none tw-mb-4">
                                <p class="tw-text-slate-800 tw-font-semibold tw-leading-relaxed">
                                    {{ $tes->tese_texto }}
                                </p>
                            </div>

                            @if(!empty($tes->tema_pure_text))
                                <p class="tw-text-slate-500 tw-text-sm tw-mb-3 tw-italic">
                                    {{ $tes->tema_pure_text }}
                                </p>
                            @endif

                            <div class="tw-flex tw-justify-start tw-items-center tw-pt-3 tw-border-t tw-border-slate-50">
                                <x-save-to-collection-btn type="tese" :tribunal="strtolower($tribunal)" :contentId="$tes->id" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="no-results-message" class="tw-hidden tw-text-center tw-py-12">
                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-rounded-full tw-bg-slate-100 tw-mb-4">
                        <i class="fa fa-search tw-text-slate-400"></i>
                    </div>
                    <h3 class="tw-text-lg tw-font-medium tw-text-slate-900">Nenhum resultado encontrado</h3>
                    <p class="tw-text-slate-500">Tente ajustar sua busca.</p>
                </div>

            </div>
        </div>

    </div>

@endsection

@section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('table-search-input');
        const clearBtn = document.getElementById('clear-search-btn');
        const toggleBtn = document.getElementById('toggle-search-btn');
        const searchContainer = document.getElementById('search-container');
        const numberOnlyCheckbox = document.getElementById('search-number-only');
        const items = document.querySelectorAll('.tese-item');
        const noResults = document.getElementById('no-results-message');
        const countSpan = document.getElementById('results-count');
        const defaultPlaceholder = searchInput.placeholder;

        toggleBtn.addEventListener('click', function() {
            searchContainer.classList.toggle('tw-hidden');
            if (!searchContainer.classList.contains('tw-hidden')) {
                searchInput.focus();
            }
        });

        numberOnlyCheckbox.addEventListener('change', function() {
            searchInput.placeholder = this.checked
                ? 'Digite o número exato do tema...'
                : defaultPlaceholder;
            searchInput.dispatchEvent(new Event('input'));
        });

        function runFilter() {
            const term = searchInput.value.trim();
            const numberOnly = numberOnlyCheckbox.checked;
            let visibleCount = 0;

            items.forEach(function(item) {
                let matches = false;
                if (term === '') {
                    matches = true;
                } else if (numberOnly) {
                    matches = (item.dataset.numero || '').trim() === term;
                } else {
                    matches = item.textContent.toLowerCase().includes(term.toLowerCase());
                }

                item.style.display = matches ? 'block' : 'none';
                if (matches) visibleCount++;
            });

            if (countSpan) countSpan.textContent = visibleCount;
            noResults.classList.toggle('tw-hidden', visibleCount > 0);
        }

        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            clearBtn.style.display = this.value.trim() ? 'inline-flex' : 'none';
            timeout = setTimeout(runFilter, 300);
        });

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    });
    </script>
@endsection
