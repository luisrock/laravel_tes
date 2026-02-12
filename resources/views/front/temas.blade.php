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

    <div class="home-pilot-shell tw-pt-4" style="{{ $display_pdf }}">
        <section class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-2">
            <h1 class="home-pilot-title tw-m-0">Pesquisas Prontas</h1>
            <p class="home-pilot-subtitle tw-m-0">
                Pesquisa pronta de súmulas, enunciados e teses de repercussão geral e repetitivos na base de dados de
                tribunais superiores e outros órgãos relevantes.
            </p>
            <p class="tw-text-sm tw-text-slate-500 tw-m-0">(todos os tribunais)</p>
        </section>
    </div>
    <!-- END Hero -->


    <!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


    <!-- Temas -->
    <div class="home-pilot-shell tw-pt-2">
        <div class="home-pilot-card tw-overflow-hidden">
            <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-px-5 tw-py-4 tw-border-b tw-border-slate-200 tw-bg-slate-50">
                <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-m-0">Temas</h3>
                @if ($admin)
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <h6 class="tw-text-xs tw-font-semibold tw-text-slate-600 tw-m-0">{{ $perc_total_concepts }}</h6>
                        <a href="{{ route('admin') }}" class="tw-text-sm tw-text-brand-700 hover:tw-text-brand-800">Admin</a>
                    </div>
                @endif
            </div>
            
            <!-- Busca Rápida -->
            <div class="tw-p-5 tw-border-b tw-border-slate-200 tw-bg-slate-50/70">
                <div class="tw-flex tw-items-stretch tw-gap-2">
                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-text-slate-500">
                            <i class="fa fa-search"></i>
                    </div>
                    <input type="text" class="home-pilot-input" id="quick-search-temas" placeholder="Filtrar temas nesta página..." autocomplete="off">
                    <div>
                        <button type="button" class="tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-text-slate-600 hover:tw-bg-slate-100" id="clear-search-temas" style="display: none;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <small class="tw-block tw-text-slate-500 tw-mt-2">
                    <span id="search-results-count"></span>
                </small>
            </div>
            <!-- END Busca Rápida -->
            
            <div class="tw-p-5">
                <div class="table-responsive">
                    <table class="home-results-table temas-grid-table table-results">
                        <!-- <thead>
                                <tr>
                                    <th style="width: 33%;">Tema</th>
                                    <th style="width: 33%;">Tema</th>
                                    <th style="width: 33%;">Tema</th>
                                </tr>
                            </thead> -->
                        <tbody>
                            <tr class="tema-row tw-block md:tw-table-row">
                                @foreach ($temas as $k => $t)
                                    @php
                                        $style = $admin && $t->concept && $t->concept_validated_at ? 'background-color: #c3d1c3;' : '';
                                    @endphp
                                        <td class="tw-font-semibold tw-text-sm tema-item tw-block md:tw-table-cell tw-w-full md:tw-w-1/3" style="{{ $style }}" data-tema-text="{{ strtolower($t->label ?? str_replace('"', '', $t->keyword)) }}">
                                        <a
                                            href="{{ route('temapage') }}/{{ $t->slug }}" class="tw-text-brand-700 hover:tw-text-brand-800">{{ $t->label ?? str_replace('"', '', $t->keyword) }}</a>
                                    </td>
                                    @if (is_int(($k + 1) / 3))
                            </tr>
                            <tr class="tema-row tw-block md:tw-table-row">
                                @endif
                                @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
    <!-- END Temas -->


    <!-- END Page Content -->

@endsection

@section('scripts')
<script>
(function() {
    const searchInput = document.getElementById('quick-search-temas');
    const clearBtn = document.getElementById('clear-search-temas');
    const resultsCount = document.getElementById('search-results-count');
    const temaItems = document.querySelectorAll('.tema-item');
    const temaRows = document.querySelectorAll('.tema-row');
    const totalTemas = temaItems.length;
    
    // Função para atualizar contagem
    function updateCount(visible) {
        if (searchInput.value.trim() === '') {
            resultsCount.textContent = '';
        } else {
            resultsCount.textContent = `Mostrando ${visible} de ${totalTemas} temas`;
        }
    }
    
    // Função de busca com debounce
    let searchTimeout;
    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            
            if (searchTerm === '') {
                // Mostrar todos
                temaItems.forEach(item => {
                    item.style.display = '';
                });
                temaRows.forEach(row => {
                    row.style.display = '';
                });
                clearBtn.style.display = 'none';
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
            
            // Ocultar linhas vazias
            temaRows.forEach(row => {
                const visibleCells = Array.from(row.querySelectorAll('.tema-item')).filter(
                    cell => cell.style.display !== 'none'
                );
                if (visibleCells.length === 0) {
                    row.style.display = 'none';
                } else {
                    row.style.display = '';
                }
            });
            
            clearBtn.style.display = 'block';
            updateCount(visibleCount);
            
            // Destacar termos encontrados
            highlightTerms(searchTerm);
        }, 200); // Debounce de 200ms
    }
    
    // Função para destacar termos
    function highlightTerms(term) {
        temaItems.forEach(item => {
            if (item.style.display === 'none') return;
            
            const link = item.querySelector('a');
            if (!link) return;
            
            const originalText = link.textContent;
            const regex = new RegExp(`(${term})`, 'gi');
            
            // Remover highlights anteriores
            link.innerHTML = originalText;
            
            // Adicionar novo highlight
            if (term && originalText.toLowerCase().includes(term)) {
                const highlightedText = originalText.replace(regex, '<mark style="background-color: #fff3cd; padding: 1px 3px; border-radius: 2px;">$1</mark>');
                link.innerHTML = highlightedText;
            }
        });
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
        performSearch();
        searchInput.focus();
    }
    
    clearBtn.addEventListener('click', clearSearch);
    
    // Focar no campo ao carregar (opcional)
    // searchInput.focus();
})();
</script>
@endsection
