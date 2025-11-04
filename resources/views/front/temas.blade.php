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

    <!-- Hero -->
    <div class="bg-body-light" style="{{ $display_pdf }}">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">
                    <a href="{{ url('/') }}">
                        Teses & Súmulas
                    </a>
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="badge badge-primary">Extensão para o Chrome</a>
                </span>
            </div>
            <p>
                Pesquisa Pronta de Súmulas, Enunciados e Teses de Repercussão Geral e Repetitivos
                feita na base de dados de tribunais superiores
                e outros órgãos relevantes. Escolha o seu tema e comece a estudar!
            </p>
            <h2>
                Pesquisas Prontas
            </h2>
            <p>
                (todos os tribunais)
            </p>
        </div>
    </div>
    <!-- END Hero -->


    <!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


    <!-- Temas -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header">
                <h3 class="block-title">Temas</h3>
                @if ($admin)
                    <h6 class="block-title">{{ $perc_total_concepts }}</h6>
                    <a href="{{ route('admin') }}">Admin</a>
                @endif
                <div class="block-options">
                    <div class="block-options-item">
                        <!-- <code>.table</code> -->
                    </div>
                </div>
            </div>
            
            <!-- Busca Rápida -->
            <div class="block-content block-content-full bg-body-light">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fa fa-search"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control form-control-alt" id="quick-search-temas" placeholder="🔍 Filtrar temas nesta página..." autocomplete="off">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary" id="clear-search-temas" style="display: none;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">
                    <span id="search-results-count"></span>
                </small>
            </div>
            <!-- END Busca Rápida -->
            
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-vcenter table-bordered">
                        <!-- <thead>
                                <tr>
                                    <th style="width: 33%;">Tema</th>
                                    <th style="width: 33%;">Tema</th>
                                    <th style="width: 33%;">Tema</th>
                                </tr>
                            </thead> -->
                        <tbody>
                            <tr class="tema-row">
                                @foreach ($temas as $k => $t)
                                    @php
                                        $style = $admin && $t->concept && $t->concept_validated_at ? 'background-color: #c3d1c3;' : '';
                                    @endphp
                                    <td class="font-w600 font-size-sm tema-item" style="{{ $style }}" data-tema-text="{{ strtolower($t->label ?? str_replace('"', '', $t->keyword)) }}">
                                        <a
                                            href="{{ route('temapage') }}/{{ $t->slug }}">{{ $t->label ?? str_replace('"', '', $t->keyword) }}</a>
                                    </td>
                                    @if (is_int(($k + 1) / 3))
                            </tr>
                            <tr class="tema-row">
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
