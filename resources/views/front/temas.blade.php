@extends('front.base')

@section('page-title', 'Temas')

@section('content')

    @php
        $admin = false;
    @endphp
    @auth
        @php
            $admin = in_array(Auth::user()->email, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com']);
        @endphp
    @endauth

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">Pesquisas Prontas</h1>
            <p class="tw-text-slate-600 tw-text-lg tw-leading-relaxed tw-m-0">
                Pesquisa pronta de súmulas, enunciados e teses de repercussão geral e repetitivos na base de dados de
                tribunais superiores e outros órgãos relevantes.
            </p>
            <p class="tw-text-sm tw-text-slate-500 tw-m-0 tw-font-medium">(todos os tribunais)</p>
        </section>
    </div>
    <!-- END Hero -->

    <!-- Temas -->
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 tw-pb-10">
        <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
            
            <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-px-6 tw-py-4 tw-border-b tw-border-slate-200 tw-bg-slate-50">
                <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-m-0">Temas Disponíveis</h3>
                @if ($admin)
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <h6 class="tw-text-xs tw-font-semibold tw-text-slate-600 tw-m-0">{{ $perc_total_concepts ?? '' }}</h6>
                        <a href="{{ route('admin') }}" class="tw-text-sm tw-text-brand-700 hover:tw-text-brand-800 hover:tw-underline">Admin</a>
                    </div>
                @endif
            </div>
            
            <!-- Busca Rápida -->
            <div class="tw-p-6 tw-border-b tw-border-slate-100">
                <div class="tw-flex tw-items-stretch tw-gap-3">
                    <div class="tw-relative tw-flex-grow">
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i class="fa fa-search tw-text-slate-400"></i>
                        </div>
                        <input type="text" class="tw-block tw-w-full tw-pl-10 tw-pr-3 tw-py-2.5 tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-900 placeholder:tw-text-slate-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500 sm:tw-text-sm" 
                            id="quick-search-temas" 
                            placeholder="Filtrar temas nesta página..." 
                            autocomplete="off">
                    </div>
                    <button class="tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-4 tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-slate-800 tw-transition-colors" type="button" id="clear-search-temas" style="display: none;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <small class="tw-block tw-mt-2 tw-text-slate-500 tw-font-medium">
                    <span id="search-results-count">Total de {{ $temas->count() }} temas</span>
                </small>
            </div>
            <!-- END Busca Rápida -->
            
            <div class="tw-p-6">
                <!-- Usando Grid Responsivo do Tailwind ao invés de tabelas -->
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4" id="temas-grid">
                    @foreach ($temas as $t)
                        @php
                            $hasConcept = $admin && $t->concept && $t->concept_validated_at;
                        @endphp
                        <div class="tema-item tw-block" data-tema-text="{{ strtolower($t->label ?? str_replace('"', '', $t->keyword)) }}">
                            <a href="{{ route('temapage') }}/{{ $t->slug }}" 
                               class="tw-block tw-h-full tw-p-4 tw-rounded-lg tw-border tw-border-slate-200 hover:tw-border-brand-300 hover:tw-bg-brand-50 hover:tw-shadow-sm tw-transition-all tw-text-slate-700 hover:tw-text-brand-800 tw-font-medium {{ $hasConcept ? 'tw-bg-emerald-50/50 tw-border-emerald-200' : 'tw-bg-white' }}">
                                {{ $t->label ?? str_replace('"', '', $t->keyword) }}
                            </a>
                        </div>
                    @endforeach
                </div>
                
                <div id="no-temas-message" class="tw-hidden tw-text-center tw-py-12">
                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-rounded-full tw-bg-slate-100 tw-mb-4">
                        <i class="fa fa-search tw-text-slate-400"></i>
                    </div>
                    <h3 class="tw-text-lg tw-font-medium tw-text-slate-900">Nenhum tema encontrado</h3>
                    <p class="tw-text-slate-500">Tente buscar por outro termo.</p>
                </div>
            </div>

        </div>

    </div>
    <!-- END Temas -->

@endsection

@section('scripts')
<script>
(function() {
    const searchInput = document.getElementById('quick-search-temas');
    const clearBtn = document.getElementById('clear-search-temas');
    const resultsCount = document.getElementById('search-results-count');
    const temaItems = document.querySelectorAll('.tema-item');
    const grid = document.getElementById('temas-grid');
    const noResults = document.getElementById('no-temas-message');
    const totalTemas = temaItems.length;
    
    // Função para atualizar contagem
    function updateCount(visible) {
        if (searchInput.value.trim() === '') {
            resultsCount.textContent = `Total de ${totalTemas} temas`;
        } else {
            resultsCount.textContent = `Mostrando ${visible} de ${totalTemas} temas`;
        }
        
        noResults.classList.toggle('tw-hidden', visible > 0);
        grid.classList.toggle('tw-hidden', visible === 0);
    }
    
    // Função de busca com debounce
    let searchTimeout;
    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            
            // Mostrar/ocultar botão limpar
            clearBtn.style.display = searchTerm ? 'inline-flex' : 'none';
            
            if (searchTerm === '') {
                // Mostrar todos
                temaItems.forEach(item => {
                    item.style.display = '';
                    // Reset highlight
                    const link = item.querySelector('a');
                    if (link) {
                        const originalText = link.textContent; // Note: this loses original HTML structure if simpler approach used
                        // Better to rely on just CSS display toggle for text search, 
                        // full highlight implementation requires storing original HTML or complex text node replacement.
                        // For simplicity/robustness in migration, skipping complex highlight logic unless crucial.
                    }
                });
                updateCount(totalTemas);
                return;
            }
            
            // Filtrar
            temaItems.forEach(item => {
                const temaText = item.getAttribute('data-tema-text');
                if (temaText.includes(searchTerm)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            updateCount(visibleCount);
            
        }, 200); // Debounce de 200ms
    }
    
    // Event listeners
    searchInput.addEventListener('input', performSearch);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            clearSearch();
        }
    });
    
    // Limpar busca
    function clearSearch() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    }
    
    if(clearBtn) clearBtn.addEventListener('click', clearSearch);
})();
</script>
@endsection
