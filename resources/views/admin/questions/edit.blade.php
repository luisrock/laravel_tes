@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .form-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .form-section h5 {
        margin-bottom: 1rem;
        color: #5c80d1;
    }
    .option-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .option-letter {
        width: 40px;
        height: 40px;
        background: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }
    .option-letter.correct {
        background: #d1fae5;
        color: #065f46;
    }
    .option-input { flex: 1; }
    .option-correct {
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: white;
    }
    .option-correct:hover { border-color: #28a745; }
    .option-correct.selected {
        background: #d1fae5;
        border-color: #28a745;
        color: #065f46;
    }
    .quiz-link {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background: #e9ecef;
        border-radius: 4px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Editar Pergunta</h2>
                    <p class="text-muted mb-0">ID: {{ $question->id }}</p>
                </div>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Usage Info -->
            @if($question->quizzes->count() > 0)
                <div class="alert alert-info">
                    <strong>Esta pergunta está em {{ $question->quizzes->count() }} quiz(zes):</strong><br>
                    @foreach($question->quizzes as $quiz)
                        <a href="{{ route('admin.quizzes.questions', $quiz) }}" class="quiz-link">{{ $quiz->title }}</a>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.questions.update', $question) }}">
                @csrf
                @method('PUT')

                <!-- Question Text -->
                <div class="form-section">
                    <h5><i class="fa fa-question-circle"></i> Enunciado</h5>
                    
                    <div class="form-group">
                        <label for="text">Texto da Pergunta <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('text') is-invalid @enderror" 
                                  id="text" name="text" rows="4" required>{{ old('text', $question->text) }}</textarea>
                        @error('text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Categoria</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Selecione...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $question->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="difficulty">Dificuldade <span class="text-danger">*</span></label>
                                <select class="form-control" id="difficulty" name="difficulty" required>
                                    <option value="easy" {{ old('difficulty', $question->difficulty) == 'easy' ? 'selected' : '' }}>Fácil</option>
                                    <option value="medium" {{ old('difficulty', $question->difficulty) == 'medium' ? 'selected' : '' }}>Intermediário</option>
                                    <option value="hard" {{ old('difficulty', $question->difficulty) == 'hard' ? 'selected' : '' }}>Difícil</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="form-section">
                    <h5><i class="fa fa-list"></i> Alternativas</h5>
                    
                    <div id="optionsContainer">
                        @php $correctOption = $question->correctOption; @endphp
                        @foreach($question->options as $index => $option)
                            <div class="option-row">
                                <span class="option-letter {{ $option->is_correct ? 'correct' : '' }}" id="letter-{{ $option->letter }}">{{ $option->letter }}</span>
                                <input type="hidden" name="options[{{ $index }}][letter]" value="{{ $option->letter }}">
                                <input type="text" class="form-control option-input" 
                                       name="options[{{ $index }}][text]" 
                                       value="{{ old('options.'.$index.'.text', $option->text) }}" required>
                                <label class="option-correct {{ $option->is_correct ? 'selected' : '' }}" onclick="selectCorrect('{{ $option->letter }}')">
                                    <input type="radio" name="correct_option" value="{{ $option->letter }}" {{ $option->is_correct ? 'checked' : '' }} style="display: none;">
                                    <i class="fa fa-check"></i> Correta
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Explanation -->
                <div class="form-section">
                    <h5><i class="fa fa-lightbulb-o"></i> Explicação</h5>
                    
                    <div class="form-group">
                        <label for="explanation">Explicação da Resposta</label>
                        <textarea class="form-control" id="explanation" name="explanation" rows="4">{{ old('explanation', $question->explanation) }}</textarea>
                    </div>
                </div>

                <!-- Tags -->
                <div class="form-section">
                    <h5><i class="fa fa-tags"></i> Tags</h5>
                    
                    <div class="form-group">
                        <div class="row">
                            @php $questionTagIds = $question->tags->pluck('id')->toArray(); @endphp
                            @foreach($tags as $tag)
                                <div class="col-md-3 col-sm-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="tag-{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}"
                                               {{ in_array($tag->id, old('tags', $questionTagIds)) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                @if($question->times_answered > 0)
                    <div class="form-section">
                        <h5><i class="fa fa-bar-chart"></i> Estatísticas</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h3>{{ $question->times_answered }}</h3>
                                    <p class="text-muted">Vezes respondida</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h3>{{ $question->times_correct }}</h3>
                                    <p class="text-muted">Acertos</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h3>{{ $question->success_rate }}%</h3>
                                    <p class="text-muted">Taxa de acerto</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Submit -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
<script>
function selectCorrect(letter) {
    document.querySelectorAll('.option-correct').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.option-letter').forEach(el => el.classList.remove('correct'));
    event.currentTarget.classList.add('selected');
    document.getElementById('letter-' + letter).classList.add('correct');
}
</script>
@endsection
