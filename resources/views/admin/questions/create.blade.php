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
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Criar Nova Pergunta</h2>
                    <p class="text-muted mb-0">A pergunta ficará disponível para ser adicionada a qualquer quiz.</p>
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

            <form method="POST" action="{{ route('admin.questions.store') }}" id="questionForm">
                @csrf

                <!-- Question Text -->
                <div class="form-section">
                    <h5><i class="fa fa-question-circle"></i> Enunciado</h5>
                    
                    <div class="form-group">
                        <label for="text">Texto da Pergunta <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('text') is-invalid @enderror" 
                                  id="text" name="text" rows="4" required
                                  placeholder="Digite o enunciado da questão...">{{ old('text') }}</textarea>
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
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                    <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Fácil</option>
                                    <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Intermediário</option>
                                    <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Difícil</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="form-section">
                    <h5><i class="fa fa-list"></i> Alternativas</h5>
                    <p class="text-muted small">Preencha as alternativas e selecione a correta.</p>
                    
                    <div id="optionsContainer">
                        @foreach(['A', 'B', 'C', 'D'] as $letter)
                            <div class="option-row">
                                <span class="option-letter" id="letter-{{ $letter }}">{{ $letter }}</span>
                                <input type="hidden" name="options[{{ $loop->index }}][letter]" value="{{ $letter }}">
                                <input type="text" class="form-control option-input @error('options.'.$loop->index.'.text') is-invalid @enderror" 
                                       name="options[{{ $loop->index }}][text]" 
                                       value="{{ old('options.'.$loop->index.'.text') }}"
                                       placeholder="Alternativa {{ $letter }}" required>
                                <label class="option-correct {{ old('correct_option') == $letter ? 'selected' : '' }}" onclick="selectCorrect('{{ $letter }}')">
                                    <input type="radio" name="correct_option" value="{{ $letter }}" {{ old('correct_option') == $letter ? 'checked' : '' }} style="display: none;">
                                    <i class="fa fa-check"></i> Correta
                                </label>
                            </div>
                        @endforeach
                    </div>
                    
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addOption()">
                        <i class="fa fa-plus"></i> Adicionar Alternativa E
                    </button>
                </div>

                <!-- Explanation -->
                <div class="form-section">
                    <h5><i class="fa fa-lightbulb-o"></i> Explicação</h5>
                    
                    <div class="form-group">
                        <label for="explanation">Explicação da Resposta (opcional)</label>
                        <textarea class="form-control" id="explanation" name="explanation" rows="4"
                                  placeholder="Explique por que a alternativa correta está certa...">{{ old('explanation') }}</textarea>
                        <small class="form-text text-muted">Aparecerá após o visitante responder a questão.</small>
                    </div>
                </div>

                <!-- Tags -->
                <div class="form-section">
                    <h5><i class="fa fa-tags"></i> Tags</h5>
                    
                    <div class="form-group">
                        <label>Selecione as tags (opcional)</label>
                        <div class="row">
                            @foreach($tags as $tag)
                                <div class="col-md-3 col-sm-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="tag-{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}"
                                               {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($tags->isEmpty())
                            <p class="text-muted small">Nenhuma tag criada ainda. <a href="{{ route('admin.questions.tags') }}">Criar tags</a></p>
                        @endif
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <div>
                        <button type="submit" name="redirect_to_create" value="1" class="btn btn-outline-primary mr-2">
                            <i class="fa fa-plus"></i> Salvar e Criar Outra
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> Salvar Pergunta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
<script>
let optionCount = 4;
const letters = ['A', 'B', 'C', 'D', 'E', 'F'];

function selectCorrect(letter) {
    // Remove selected from all
    document.querySelectorAll('.option-correct').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.option-letter').forEach(el => el.classList.remove('correct'));
    
    // Add selected to clicked
    event.currentTarget.classList.add('selected');
    document.getElementById('letter-' + letter).classList.add('correct');
}

function addOption() {
    if (optionCount >= 6) {
        alert('Máximo de 6 alternativas');
        return;
    }
    
    const letter = letters[optionCount];
    const container = document.getElementById('optionsContainer');
    
    const row = document.createElement('div');
    row.className = 'option-row';
    row.innerHTML = `
        <span class="option-letter" id="letter-${letter}">${letter}</span>
        <input type="hidden" name="options[${optionCount}][letter]" value="${letter}">
        <input type="text" class="form-control option-input" name="options[${optionCount}][text]" placeholder="Alternativa ${letter}" required>
        <label class="option-correct" onclick="selectCorrect('${letter}')">
            <input type="radio" name="correct_option" value="${letter}" style="display: none;">
            <i class="fa fa-check"></i> Correta
        </label>
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeOption(this, '${letter}')">
            <i class="fa fa-times"></i>
        </button>
    `;
    
    container.appendChild(row);
    optionCount++;
    
    // Update button text
    if (optionCount >= 6) {
        document.querySelector('[onclick="addOption()"]').style.display = 'none';
    } else {
        document.querySelector('[onclick="addOption()"]').innerHTML = '<i class="fa fa-plus"></i> Adicionar Alternativa ' + letters[optionCount];
    }
}

function removeOption(button, letter) {
    button.closest('.option-row').remove();
    optionCount--;
    
    // Update button
    document.querySelector('[onclick="addOption()"]').style.display = 'inline-block';
    document.querySelector('[onclick="addOption()"]').innerHTML = '<i class="fa fa-plus"></i> Adicionar Alternativa ' + letters[optionCount];
}
</script>
@endsection
