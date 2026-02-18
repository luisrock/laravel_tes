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
                                placeholder="Pesquisar por tema, número, texto da tese, ramo...">
                        </div>
                        <button class="tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-4 tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-slate-800 tw-transition-colors" type="button" id="clear-search-btn" style="display:none;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <small class="tw-block tw-mt-2 tw-text-slate-500">Digite para filtrar instantaneamente.</small>
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
                    @foreach ($teses as $tes)
                        <div class="tese-item tw-block tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-6 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0">
                                    <a href="{{ route($tese_route, ['tese' => $tes->id]) }}" class="tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline tw-underline-offset-2 {{ $tes->isCancelada ? 'tw-text-slate-500 tw-line-through' : '' }}">
                                        TEMA {{ $tes->numero }}
                                    </a>
                                </h4>
                                <div class="tw-flex tw-items-center tw-gap-2">
                                    @if(!empty($tes->ramo))
                                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">
                                            {{ $tes->ramo }}
                                        </span>
                                    @endif
                                    @if(!empty($tes->situacao))
                                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium {{ $tes->isCancelada ? 'tw-bg-red-100 tw-text-red-800' : 'tw-bg-slate-100 tw-text-slate-600' }}">
                                            {{ $tes->situacao }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if(!empty($tes->tema_pure_text))
                                <p class="tw-text-slate-500 tw-text-sm tw-mb-3 tw-italic">
                                    {{ $tes->tema_pure_text }}
                                </p>
                            @endif

                            <div class="tw-prose tw-prose-slate tw-max-w-none tw-mb-4">
                                <p class="tw-text-slate-800 tw-font-semibold tw-leading-relaxed">
                                    {{ $tes->tese_texto ?: '[aguarda julgamento]' }}
                                </p>
                            </div>

                            @if(!empty($tes->tempo))
                                <div class="tw-flex tw-justify-end tw-items-center tw-pt-3 tw-border-t tw-border-slate-50 tw-text-xs tw-text-slate-500">
                                    <span>{{ $tes->tempo }}</span>
                                </div>
                            @endif
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
        const items = document.querySelectorAll('.tese-item');
        const noResults = document.getElementById('no-results-message');
        const countSpan = document.getElementById('results-count');

        toggleBtn.addEventListener('click', function() {
            searchContainer.classList.toggle('tw-hidden');
            if (!searchContainer.classList.contains('tw-hidden')) {
                searchInput.focus();
            }
        });

        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const term = this.value.toLowerCase().trim();

            clearBtn.style.display = term ? 'inline-flex' : 'none';

            timeout = setTimeout(function() {
                let visibleCount = 0;

                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(term)) {
                        item.style.display = 'block';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (countSpan) {
                    countSpan.textContent = visibleCount;
                }
                noResults.classList.toggle('tw-hidden', visibleCount > 0);

            }, 300);
        });

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    });
    </script>
@endsection
