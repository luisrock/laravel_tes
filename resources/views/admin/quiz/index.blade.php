@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .quiz-card {
        border-left: 4px solid #5c80d1;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    .quiz-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .quiz-color-badge {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
    }
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .status-draft { background: #fef3c7; color: #92400e; }
    .status-published { background: #d1fae5; color: #065f46; }
    .status-archived { background: #e5e7eb; color: #374151; }
    .difficulty-easy { color: #10b981; }
    .difficulty-medium { color: #f59e0b; }
    .difficulty-hard { color: #ef4444; }
    .filter-form {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .quiz-actions .btn { margin-left: 0.25rem; }
    .quiz-actions .btn i { margin-right: 0.25rem; }
    @media (max-width: 992px) {
        .quiz-actions .btn span.btn-label { display: none; }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Quizzes</h2>
                    <p class="text-muted mb-0">Gerencie os quizzes do site</p>
                </div>
                <div class="d-flex align-items-center">
                    <!-- Toggle Home Visibility -->
                    <form action="{{ route('admin.quizzes.toggle-home') }}" method="POST" class="mr-3">
                        @csrf
                        @if($isVisibleOnHome)
                            <button type="submit" class="btn btn-success" title="Clique para ocultar da home">
                                <i class="fas fa-eye"></i> Visível na Home
                            </button>
                        @else
                            <button type="submit" class="btn btn-outline-secondary" title="Clique para publicar na home">
                                <i class="fas fa-eye-slash"></i> Oculto na Home
                            </button>
                        @endif
                    </form>
                    
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-primary mr-2">
                        <i class="fas fa-list"></i> Banco de Perguntas
                    </a>
                    <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Quiz
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Filters -->
            <div class="filter-form">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-3">
                        <label>Buscar</label>
                        <input type="text" name="search" class="form-control" placeholder="Título ou descrição..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Tribunal</label>
                        <select name="tribunal" class="form-control">
                            <option value="">Todos</option>
                            <option value="STF" {{ request('tribunal') == 'STF' ? 'selected' : '' }}>STF</option>
                            <option value="STJ" {{ request('tribunal') == 'STJ' ? 'selected' : '' }}>STJ</option>
                            <option value="TST" {{ request('tribunal') == 'TST' ? 'selected' : '' }}>TST</option>
                            <option value="TNU" {{ request('tribunal') == 'TNU' ? 'selected' : '' }}>TNU</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Categoria</label>
                        <select name="category_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </form>
            </div>

            <!-- Quiz List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $quizzes->total() }} quiz(zes) encontrado(s)</span>
                </div>
                <div class="card-body">
                    @forelse($quizzes as $quiz)
                        <div class="card quiz-card" style="border-left-color: {{ $quiz->color }};">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="quiz-color-badge mr-2" style="background-color: {{ $quiz->color }};"></span>
                                            <h5 class="mb-0">{{ $quiz->title }}</h5>
                                            <span class="status-badge status-{{ $quiz->status }} ml-2">{{ $quiz->status_label }}</span>
                                        </div>
                                        <p class="text-muted mb-2">{{ Str::limit($quiz->description, 150) }}</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @if($quiz->tribunal)
                                                <span class="badge badge-primary">{{ $quiz->tribunal }}</span>
                                            @endif
                                            @if($quiz->tema_number)
                                                <span class="badge badge-secondary">Tema {{ $quiz->tema_number }}</span>
                                            @endif
                                            @if($quiz->category)
                                                <span class="badge badge-info">{{ $quiz->category->name }}</span>
                                            @endif
                                            <span class="badge difficulty-{{ $quiz->difficulty }}">
                                                {{ $quiz->difficulty_label }}
                                            </span>
                                            <span class="badge badge-light">
                                                {{ $quiz->questions_count }} pergunta(s)
                                            </span>
                                            <span class="badge badge-light">
                                                ~{{ $quiz->estimated_time }} min
                                            </span>
                                            <span class="badge badge-light">
                                                {{ $quiz->views_count }} visualizações
                                            </span>
                                        </div>
                                    </div>
                                    <div class="quiz-actions">
                                        <a href="{{ route('admin.quizzes.questions', $quiz) }}" class="btn btn-sm btn-outline-primary" title="Gerenciar Perguntas">
                                            <i class="fas fa-list-ol"></i> <span class="btn-label">Gerenciar Perguntas</span>
                                        </a>
                                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                            <i class="fas fa-pencil"></i> <span class="btn-label">Editar</span>
                                        </a>
                                        <a href="{{ route('admin.quizzes.duplicate', $quiz) }}" class="btn btn-sm btn-outline-info" title="Duplicar">
                                            <i class="fas fa-copy"></i> <span class="btn-label">Duplicar</span>
                                        </a>
                                        @if($quiz->status == 'published')
                                            <a href="{{ route('quiz.show', $quiz->slug) }}" class="btn btn-sm btn-outline-success" target="_blank" title="Ver no site">
                                                <i class="fas fa-external-link-alt"></i> <span class="btn-label">Ver no site</span>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este quiz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="fas fa-trash"></i> <span class="btn-label">Excluir</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum quiz encontrado.</p>
                            <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeiro Quiz
                            </a>
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $quizzes->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

            <!-- Back to Admin -->
            <div class="mt-3">
                <a href="{{ route('admin') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Voltar ao Admin
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
