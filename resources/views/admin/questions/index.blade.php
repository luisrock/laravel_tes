@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .question-card {
        border-left: 4px solid #5c80d1;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .question-card.unused {
        border-left-color: #ffc107;
    }
    .filter-form {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .option-preview {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        background: #e9ecef;
        border-radius: 4px;
        margin-right: 0.25rem;
        font-size: 0.8rem;
    }
    .option-preview.correct {
        background: #d1fae5;
        color: #065f46;
    }
    .question-actions .btn { margin-left: 0.25rem; }
    .question-actions .btn i { margin-right: 0.25rem; }
    @media (max-width: 992px) {
        .question-actions .btn span.btn-label { display: none; }
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
                    <h2>Banco de Perguntas</h2>
                    <p class="text-muted mb-0">Gerencie todas as perguntas disponíveis para os quizzes</p>
                </div>
                <div>
                    <a href="{{ route('admin.questions.tags') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fa fa-tags"></i> Tags
                    </a>
                    <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fa fa-brain"></i> Quizzes
                    </a>
                    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Nova Pergunta
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
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Filters -->
            <div class="filter-form">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-3">
                        <label>Buscar</label>
                        <input type="text" name="search" class="form-control" placeholder="Buscar no enunciado..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
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
                        <label>Dificuldade</label>
                        <select name="difficulty" class="form-control">
                            <option value="">Todas</option>
                            <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Fácil</option>
                            <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Intermediário</option>
                            <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Difícil</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Tag</label>
                        <select name="tag_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="unused" name="unused" value="1" {{ request('unused') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="unused">Não usadas</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </form>
            </div>

            <!-- Questions List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $questions->total() }} pergunta(s) encontrada(s)</span>
                </div>
                <div class="card-body">
                    @forelse($questions as $question)
                        <div class="card question-card {{ $question->quizzes_count == 0 ? 'unused' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <p class="mb-2"><strong>{{ Str::limit($question->text, 200) }}</strong></p>
                                        
                                        <div class="mb-2">
                                            @foreach($question->options as $option)
                                                <span class="option-preview {{ $option->is_correct ? 'correct' : '' }}">
                                                    {{ $option->letter }}{{ $option->is_correct ? ' ✓' : '' }}
                                                </span>
                                            @endforeach
                                        </div>
                                        
                                        <div class="d-flex flex-wrap gap-2">
                                            @if($question->category)
                                                <span class="badge badge-info">{{ $question->category->name }}</span>
                                            @endif
                                            <span class="badge badge-{{ $question->difficulty == 'easy' ? 'success' : ($question->difficulty == 'hard' ? 'danger' : 'warning') }}">
                                                {{ $question->difficulty_label }}
                                            </span>
                                            @foreach($question->tags as $tag)
                                                <span class="badge badge-secondary">{{ $tag->name }}</span>
                                            @endforeach
                                            <span class="badge badge-light">
                                                Em {{ $question->quizzes_count }} quiz(zes)
                                            </span>
                                            @if($question->times_answered > 0)
                                                <span class="badge badge-light">
                                                    {{ $question->success_rate }}% de acerto
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="question-actions ml-3">
                                        <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fa fa-pencil"></i> <span class="btn-label">Editar</span>
                                        </a>
                                        <a href="{{ route('admin.questions.duplicate', $question) }}" class="btn btn-sm btn-outline-info" title="Duplicar">
                                            <i class="fa fa-copy"></i> <span class="btn-label">Duplicar</span>
                                        </a>
                                        <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir" {{ $question->quizzes_count > 0 ? 'disabled' : '' }}>
                                                <i class="fa fa-trash"></i> <span class="btn-label">Excluir</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fa fa-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhuma pergunta encontrada.</p>
                            <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Criar Primeira Pergunta
                            </a>
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $questions->appends(request()->query())->links() }}
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
