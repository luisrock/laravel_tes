@extends('layouts.app')

@section('admin-styles')
    <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                    @auth
                    @if(in_array(Auth::user()->email, ['mauluis@gmail.com','trator70@gmail.com','ivanaredler@gmail.com']))
                    <br><a href="{{ route('searchpage') }}">Pesquisa</a> | <a href="{{ route('alltemaspage') }}">Temas</a> 
                    @endif
                    @endauth
                </div>

            </div>
        </div>
        
        <div class="col-md-12" style="margin-top:50px">
            <div class="card">
                <div class="card-header" style="display:flex; justify-content: space-between; align-items: center;">
                    <a href="{{ route('alltemaspage') }}">Temas</a>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button id="toggle-created">hide created</button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- CSRF Token para JavaScript -->
                    @csrf

                    <!-- Controles de Filtro e Paginação -->
                    <div class="admin-controls">
                        <div class="admin-controls-row">
                            <!-- Busca -->
                            <div class="admin-control-group">
                                <label for="search-input">Buscar</label>
                                <input type="text" id="search-input" placeholder="Digite keyword ou label..." class="form-control">
                            </div>

                            <!-- Filtro por Status -->
                            <div class="admin-control-group">
                                <label for="filter-status">Filtrar por Status</label>
                                <select id="filter-status" class="form-control">
                                    <option value="all">Todos</option>
                                    <option value="not_created" selected>Não Criados</option>
                                    <option value="created">Criados</option>
                                    <option value="checked">Verificados</option>
                                    <option value="pending">Pendentes (não criados e não verificados)</option>
                                </select>
                            </div>

                            <!-- Ordenar por -->
                            <div class="admin-control-group">
                                <label for="order-by">Ordenar por</label>
                                <select id="order-by" class="form-control">
                                    <option value="keyword">Alfabética (Keyword)</option>
                                    <option value="results" selected>Número de Resultados</option>
                                    <option value="created_at">Data de Criação</option>
                                </select>
                            </div>

                            <!-- Direção -->
                            <div class="admin-control-group">
                                <label for="order-direction">Direção</label>
                                <select id="order-direction" class="form-control">
                                    <option value="asc">Crescente (A-Z, 0-9)</option>
                                    <option value="desc" selected>Decrescente (Z-A, 9-0)</option>
                                </select>
                            </div>

                            <!-- Por página -->
                            <div class="admin-control-group">
                                <label for="per-page">Por página</label>
                                <select id="per-page" class="form-control">
                                    <option value="30">30</option>
                                    <option value="60" selected>60</option>
                                    <option value="120">120</option>
                                </select>
                            </div>
                        </div>

                        <!-- Estatísticas -->
                        <div class="admin-stats">
                            <div class="admin-stat-item">
                                <span class="admin-stat-label">Total:</span>
                                <span class="admin-stat-value" id="stat-total">{{ $stats['total'] }}</span>
                            </div>
                            <div class="admin-stat-item">
                                <span class="admin-stat-label">Criados:</span>
                                <span class="admin-stat-value" id="stat-created">{{ $stats['created'] }}</span>
                            </div>
                            <div class="admin-stat-item">
                                <span class="admin-stat-label">Verificados:</span>
                                <span class="admin-stat-value" id="stat-checked">{{ $stats['checked'] }}</span>
                            </div>
                            <div class="admin-stat-item">
                                <span class="admin-stat-label">Pendentes:</span>
                                <span class="admin-stat-value" id="stat-pending">{{ $stats['pending'] }}</span>
                            </div>
                            <div class="admin-stat-item">
                                <span class="admin-stat-label">Exibindo:</span>
                                <span class="admin-stat-value" id="stat-showing">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Controles de Seleção em Massa -->
                    <div class="batch-controls" id="batch-controls">
                        <div class="batch-controls-inner">
                            <button id="select-all-page" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-check-square-o"></i> Marcar todos desta página
                            </button>
                            <button id="deselect-all" class="btn btn-sm btn-outline-secondary" style="display: none;">
                                <i class="fa fa-square-o"></i> Desmarcar todos
                            </button>
                            <div class="batch-selection-info" id="batch-selection-info" style="display: none;">
                                <span class="badge badge-warning" style="font-size: 1rem; padding: 8px 15px;">
                                    <strong id="selected-count">0</strong> selecionados
                                </span>
                                <button id="delete-selected" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i> Deletar selecionados
                                </button>
                            </div>
                        </div>
                    </div>

                <div class="block-content" id="temas-container">
                    <div class="admin-loading">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Carregando temas...</p>
                    </div>
                </div>

                <!-- Paginação -->
                <div class="admin-pagination" id="pagination-container" style="display: none;">
                    <div class="pagination-info" id="pagination-info"></div>
                    <div class="pagination-controls" id="pagination-controls"></div>
                </div>
                    
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="{{ asset('assets/js/admin.js') }}"></script>
@endsection

@section('adminjs')
<script>
// Definir URLs das rotas para o admin.js
var adminRoutes = {
    store: "{{ route('adminstore') }}",
    delete: "{{ route('admindel') }}",
    getTemas: "{{ route('admin.getTemas') }}"
};

// Atualizar URLs no admin.js se necessário
if (typeof window.adminRoutes === 'undefined') {
    window.adminRoutes = adminRoutes;
}
</script>
@endsection
