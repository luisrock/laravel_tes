@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .color-option {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        border: 3px solid transparent;
        transition: all 0.2s ease;
        display: inline-block;
        margin-right: 8px;
    }
    .color-option:hover { transform: scale(1.1); }
    .color-option.selected { border-color: #333; }
    .color-preview {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: inline-block;
        vertical-align: middle;
    }
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
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Criar Novo Quiz</h2>
                    <p class="text-muted mb-0">Preencha as informações básicas. As perguntas serão adicionadas depois.</p>
                </div>
                <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
            </div>

            <form method="POST" action="{{ route('admin.quizzes.store') }}">
                @csrf

                <!-- Basic Info -->
                <div class="form-section">
                    <h5><i class="fa fa-info-circle"></i> Informações Básicas</h5>
                    
                    <div class="form-group">
                        <label for="title">Título do Quiz <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required
                               placeholder="Ex: Prescrição e Reconhecimento de Dívida">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tribunal">Tribunal</label>
                                <select class="form-control @error('tribunal') is-invalid @enderror" id="tribunal" name="tribunal">
                                    <option value="">Selecione...</option>
                                    <option value="STF" {{ old('tribunal') == 'STF' ? 'selected' : '' }}>STF</option>
                                    <option value="STJ" {{ old('tribunal') == 'STJ' ? 'selected' : '' }}>STJ</option>
                                    <option value="TST" {{ old('tribunal') == 'TST' ? 'selected' : '' }}>TST</option>
                                    <option value="TNU" {{ old('tribunal') == 'TNU' ? 'selected' : '' }}>TNU</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tema_number">Número do Tema (opcional)</label>
                                <input type="number" class="form-control @error('tema_number') is-invalid @enderror" 
                                       id="tema_number" name="tema_number" value="{{ old('tema_number') }}"
                                       placeholder="Ex: 999">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Categoria</label>
                                <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
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
                                <label for="estimated_time">Tempo Estimado (minutos) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('estimated_time') is-invalid @enderror" 
                                       id="estimated_time" name="estimated_time" value="{{ old('estimated_time', 2) }}" 
                                       min="1" max="120" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="difficulty">Dificuldade <span class="text-danger">*</span></label>
                        <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                            <label class="btn btn-outline-success {{ old('difficulty', 'medium') == 'easy' ? 'active' : '' }}">
                                <input type="radio" name="difficulty" value="easy" {{ old('difficulty', 'medium') == 'easy' ? 'checked' : '' }}> Fácil
                            </label>
                            <label class="btn btn-outline-warning {{ old('difficulty', 'medium') == 'medium' ? 'active' : '' }}">
                                <input type="radio" name="difficulty" value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'checked' : '' }}> Intermediário
                            </label>
                            <label class="btn btn-outline-danger {{ old('difficulty', 'medium') == 'hard' ? 'active' : '' }}">
                                <input type="radio" name="difficulty" value="hard" {{ old('difficulty', 'medium') == 'hard' ? 'checked' : '' }}> Difícil
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Descrição / Meta Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3"
                                  placeholder="Descrição breve para SEO (150-160 caracteres recomendado)">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Color -->
                <div class="form-section">
                    <h5><i class="fa fa-palette"></i> Cor do Quiz</h5>
                    
                    <div class="form-group">
                        <label>Selecione uma cor:</label>
                        <div class="mb-3">
                            @foreach($colors as $color)
                                <div class="color-option {{ old('color', '#912F56') == $color ? 'selected' : '' }}" 
                                     style="background-color: {{ $color }};" 
                                     data-color="{{ $color }}"
                                     onclick="selectColor('{{ $color }}')"></div>
                            @endforeach
                        </div>
                        <div class="d-flex align-items-center">
                            <label class="mr-2 mb-0">Personalizada:</label>
                            <input type="color" id="customColor" value="{{ old('color', '#912F56') }}" 
                                   style="width: 50px; height: 40px; border: none; cursor: pointer;"
                                   onchange="selectColor(this.value)">
                            <input type="text" class="form-control ml-2" id="colorHex" name="color" 
                                   value="{{ old('color', '#912F56') }}" style="width: 100px;" 
                                   pattern="^#[0-9A-Fa-f]{6}$" required>
                            <div class="color-preview ml-3" id="colorPreview" style="background-color: {{ old('color', '#912F56') }};"></div>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="form-section">
                    <h5><i class="fa fa-cog"></i> Opções de Exibição</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="show_ads" name="show_ads" value="1" {{ old('show_ads', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="show_ads">Exibir Anúncios</label>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="show_share" name="show_share" value="1" {{ old('show_share', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="show_share">Botões de Compartilhar</label>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="show_progress" name="show_progress" value="1" {{ old('show_progress', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="show_progress">Barra de Progresso</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="random_order" name="random_order" value="1" {{ old('random_order') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="random_order">Ordem Aleatória das Perguntas</label>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="show_feedback_immediately" name="show_feedback_immediately" value="1" {{ old('show_feedback_immediately', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="show_feedback_immediately">Feedback Imediato (senão, só no final)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="form-section">
                    <h5><i class="fa fa-search"></i> SEO</h5>
                    
                    <div class="form-group">
                        <label for="slug">Slug da URL (gerado automaticamente se vazio)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">/quiz/</span>
                            </div>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" value="{{ old('slug') }}"
                                   placeholder="prescricao-reconhecimento-divida">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meta_keywords">Palavras-chave (separadas por vírgula)</label>
                        <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                               id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}"
                               placeholder="quiz jurídico, teses STF, prescrição">
                    </div>
                </div>

                <!-- Status -->
                <div class="form-section">
                    <h5><i class="fa fa-flag"></i> Status</h5>
                    
                    <div class="form-group">
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                        </select>
                        <small class="form-text text-muted">Recomendamos criar como rascunho e publicar após adicionar as perguntas.</small>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> Criar Quiz e Adicionar Perguntas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
<script>
function selectColor(color) {
    // Remove selected from all
    document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
    
    // Find matching palette color
    const paletteOption = document.querySelector(`.color-option[data-color="${color}"]`);
    if (paletteOption) {
        paletteOption.classList.add('selected');
    }
    
    // Update inputs
    document.getElementById('colorHex').value = color;
    document.getElementById('customColor').value = color;
    document.getElementById('colorPreview').style.backgroundColor = color;
}

// Initialize
document.getElementById('colorHex').addEventListener('input', function(e) {
    if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
        selectColor(e.target.value);
    }
});
</script>
@endsection
