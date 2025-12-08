@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .question-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }
    .question-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .question-header {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: move;
    }
    .question-number {
        width: 32px;
        height: 32px;
        background: {{ $quiz->color }};
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    .question-text {
        flex: 1;
        font-size: 0.9rem;
    }
    .question-meta {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    .search-results {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .search-result-item {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .search-result-item:hover {
        background: #f8f9fa;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
    .modal-body-scroll {
        max-height: 60vh;
        overflow-y: auto;
    }
    .option-badge {
        display: inline-block;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        text-align: center;
        line-height: 24px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    .option-badge.correct {
        background: #d1fae5;
        color: #065f46;
    }
    .option-badge.incorrect {
        background: #e5e7eb;
        color: #374151;
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
                    <h2>Perguntas do Quiz</h2>
                    <p class="text-muted mb-0">{{ $quiz->title }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-outline-secondary mr-2">
                        <i class="fa fa-pencil"></i> Editar Quiz
                    </a>
                    <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="row">
                <!-- Questions List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><strong>{{ $quiz->questions->count() }}</strong> pergunta(s) neste quiz</span>
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addQuestionModal">
                                <i class="fa fa-plus"></i> Adicionar Pergunta
                            </button>
                        </div>
                        <div class="card-body">
                            @if($quiz->questions->count() > 0)
                                <div id="questionsList">
                                    @foreach($quiz->questions as $index => $question)
                                        <div class="question-item" data-id="{{ $question->id }}">
                                            <div class="question-header">
                                                <i class="fa fa-grip-vertical text-muted"></i>
                                                <span class="question-number">{{ $index + 1 }}</span>
                                                <div class="question-text">
                                                    {{ Str::limit($question->text, 150) }}
                                                </div>
                                                <div class="question-meta">
                                                    @if($question->category)
                                                        <span class="badge badge-info">{{ $question->category->name }}</span>
                                                    @endif
                                                    <span class="badge badge-secondary">{{ $question->options->count() }} opções</span>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewQuestion({{ $question->id }})" title="Ver detalhes">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="removeQuestion({{ $question->id }})" title="Remover do quiz">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-muted small mt-3">
                                    <i class="fa fa-info-circle"></i> Arraste as perguntas para reordenar.
                                </p>
                            @else
                                <div class="empty-state">
                                    <i class="fa fa-list-ol fa-3x mb-3"></i>
                                    <p>Nenhuma pergunta neste quiz ainda.</p>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#addQuestionModal">
                                        <i class="fa fa-plus"></i> Adicionar Primeira Pergunta
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Quiz Info -->
                    <div class="card mb-3">
                        <div class="card-header">Informações do Quiz</div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Status:</strong>
                                <span class="badge badge-{{ $quiz->status == 'published' ? 'success' : ($quiz->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ $quiz->status_label }}
                                </span>
                            </div>
                            @if($quiz->tribunal)
                                <div class="mb-2"><strong>Tribunal:</strong> {{ $quiz->tribunal }}</div>
                            @endif
                            @if($quiz->category)
                                <div class="mb-2"><strong>Categoria:</strong> {{ $quiz->category->name }}</div>
                            @endif
                            <div class="mb-2"><strong>Dificuldade:</strong> {{ $quiz->difficulty_label }}</div>
                            <div class="mb-2"><strong>Tempo:</strong> ~{{ $quiz->estimated_time }} min</div>
                            <div class="mb-2">
                                <strong>Cor:</strong>
                                <span style="display: inline-block; width: 20px; height: 20px; background: {{ $quiz->color }}; border-radius: 4px; vertical-align: middle;"></span>
                                {{ $quiz->color }}
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">Ações Rápidas</div>
                        <div class="card-body">
                            <a href="{{ route('admin.questions.create') }}?quiz_id={{ $quiz->id }}" class="btn btn-outline-primary btn-block mb-2">
                                <i class="fa fa-plus"></i> Criar Nova Pergunta
                            </a>
                            <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary btn-block mb-2">
                                <i class="fa fa-database"></i> Banco de Perguntas
                            </a>
                            @if($quiz->status == 'published')
                                <a href="{{ route('quiz.show', $quiz->slug) }}" class="btn btn-outline-success btn-block" target="_blank">
                                    <i class="fa fa-external-link"></i> Ver no Site
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Pergunta ao Quiz</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Search -->
                <div class="form-group">
                    <label>Buscar no banco de perguntas:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchQuestion" placeholder="Digite para buscar...">
                        <div class="input-group-append">
                            <select class="form-control" id="searchCategory">
                                <option value="">Todas categorias</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Results -->
                <div class="search-results" id="searchResults">
                    <div class="empty-state py-4">
                        <p class="mb-0">Digite algo para buscar perguntas existentes.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.questions.create') }}?quiz_id={{ $quiz->id }}" class="btn btn-outline-primary mr-auto">
                    <i class="fa fa-plus"></i> Criar Nova Pergunta
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- View Question Modal -->
<div class="modal fade" id="viewQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Pergunta</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="questionDetails">
                <div class="text-center py-4">
                    <i class="fa fa-spinner fa-spin"></i> Carregando...
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const quizId = {{ $quiz->id }};
const csrfToken = '{{ csrf_token() }}';

// Initialize sortable
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('questionsList');
    if (list) {
        new Sortable(list, {
            animation: 150,
            handle: '.fa-grip-vertical',
            onEnd: function() {
                updateOrder();
            }
        });
    }
});

// Update order
function updateOrder() {
    const items = document.querySelectorAll('#questionsList .question-item');
    const order = Array.from(items).map(item => item.dataset.id);
    
    fetch(`/admin/quizzes/${quizId}/questions/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ order: order })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update numbers
            items.forEach((item, index) => {
                item.querySelector('.question-number').textContent = index + 1;
            });
        }
    });
}

// Search questions
let searchTimeout;
document.getElementById('searchQuestion').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => searchQuestions(), 300);
});

document.getElementById('searchCategory').addEventListener('change', searchQuestions);

function searchQuestions() {
    const query = document.getElementById('searchQuestion').value;
    const categoryId = document.getElementById('searchCategory').value;
    
    if (query.length < 2 && !categoryId) {
        document.getElementById('searchResults').innerHTML = '<div class="empty-state py-4"><p class="mb-0">Digite algo para buscar perguntas existentes.</p></div>';
        return;
    }
    
    document.getElementById('searchResults').innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Buscando...</div>';
    
    fetch(`/admin/quizzes/${quizId}/questions/search?q=${encodeURIComponent(query)}&category_id=${categoryId}`)
    .then(response => response.json())
    .then(data => {
        if (data.questions.length === 0) {
            document.getElementById('searchResults').innerHTML = '<div class="empty-state py-4"><p class="mb-0">Nenhuma pergunta encontrada.</p></div>';
            return;
        }
        
        let html = '';
        data.questions.forEach(question => {
            const correctOption = question.options.find(o => o.is_correct);
            html += `
                <div class="search-result-item" onclick="addQuestion(${question.id})">
                    <div class="mb-2"><strong>${question.text.substring(0, 200)}${question.text.length > 200 ? '...' : ''}</strong></div>
                    <div class="small text-muted">
                        ${question.category ? `<span class="badge badge-info">${question.category.name}</span>` : ''}
                        <span class="badge badge-secondary">${question.options.length} opções</span>
                        ${correctOption ? `<span class="badge badge-success">Resposta: ${correctOption.letter}</span>` : ''}
                    </div>
                </div>
            `;
        });
        document.getElementById('searchResults').innerHTML = html;
    });
}

// Add question to quiz
function addQuestion(questionId) {
    fetch(`/admin/quizzes/${quizId}/questions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ question_id: questionId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erro ao adicionar pergunta');
        }
    });
}

// Remove question from quiz
function removeQuestion(questionId) {
    if (!confirm('Remover esta pergunta do quiz?')) return;
    
    fetch(`/admin/quizzes/${quizId}/questions/${questionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erro ao remover pergunta');
        }
    });
}

// View question details
function viewQuestion(questionId) {
    document.getElementById('questionDetails').innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>';
    $('#viewQuestionModal').modal('show');
    
    // Find question in current quiz
    const questions = @json($quiz->questions);
    const question = questions.find(q => q.id === questionId);
    
    if (question) {
        let optionsHtml = '';
        question.options.forEach(opt => {
            optionsHtml += `
                <div class="mb-2">
                    <span class="option-badge ${opt.is_correct ? 'correct' : 'incorrect'}">${opt.letter}</span>
                    ${opt.text}
                    ${opt.is_correct ? '<i class="fa fa-check text-success ml-2"></i>' : ''}
                </div>
            `;
        });
        
        document.getElementById('questionDetails').innerHTML = `
            <div class="mb-3">
                <h6>Enunciado:</h6>
                <p>${question.text}</p>
            </div>
            <div class="mb-3">
                <h6>Alternativas:</h6>
                ${optionsHtml}
            </div>
            ${question.explanation ? `
                <div class="mb-3">
                    <h6>Explicação:</h6>
                    <p class="text-muted">${question.explanation}</p>
                </div>
            ` : ''}
            <div class="mt-3">
                <a href="/admin/questions/${question.id}/edit" class="btn btn-primary">
                    <i class="fa fa-pencil"></i> Editar Pergunta
                </a>
            </div>
        `;
    }
}
</script>
@endsection
