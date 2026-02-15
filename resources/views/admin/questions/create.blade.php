@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <div class="tw-max-w-4xl tw-mx-auto">
        <!-- Header -->
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-8 tw-gap-4">
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Criar Nova Pergunta</h1>
                <p class="tw-text-slate-500">A pergunta ficará disponível para ser adicionada a qualquer quiz.</p>
            </div>
            <a href="{{ route('admin.questions.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fa fa-arrow-left tw-mr-2"></i> Voltar
            </a>
        </div>

        @if (session('success'))
            <div class="tw-bg-emerald-50 tw-text-emerald-700 tw-p-4 tw-rounded-lg tw-mb-6 tw-border tw-border-emerald-200 tw-flex tw-justify-between tw-items-center">
                <div><i class="fas fa-check-circle tw-mr-2"></i> {{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="tw-text-emerald-500 hover:tw-text-emerald-700"><i class="fas fa-times"></i></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.questions.store') }}" id="questionForm">
            @csrf

            <!-- Question Text -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-question-circle tw-text-brand-500"></i> Enunciado
                </h2>
                
                <div class="tw-mb-4">
                    <label for="text" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Texto da Pergunta <span class="tw-text-rose-500">*</span></label>
                    <textarea class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('text') tw-border-rose-500 @enderror" 
                              id="text" name="text" rows="4" required
                              placeholder="Digite o enunciado da questão...">{{ old('text') }}</textarea>
                    @error('text')
                        <p class="tw-text-xs tw-text-rose-500 tw-mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div>
                        <label for="category_id" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Categoria</label>
                        <select class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" id="category_id" name="category_id">
                            <option value="">Selecione...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="difficulty" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Dificuldade <span class="tw-text-rose-500">*</span></label>
                        <select class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" id="difficulty" name="difficulty" required>
                            <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Fácil</option>
                            <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Intermediário</option>
                            <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Difícil</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-2 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-list tw-text-brand-500"></i> Alternativas
                </h2>
                <p class="tw-text-slate-500 tw-text-sm tw-mb-6">Preencha as alternativas e selecione a correta.</p>
                
                <div id="optionsContainer" class="tw-space-y-4 tw-mb-4">
                    @foreach(['A', 'B', 'C', 'D'] as $letter)
                        <div class="option-row tw-flex tw-items-center tw-gap-3">
                            <span class="option-letter tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-slate-100 tw-font-bold tw-text-slate-600 tw-flex-shrink-0" id="letter-{{ $letter }}">{{ $letter }}</span>
                            <input type="hidden" name="options[{{ $loop->index }}][letter]" value="{{ $letter }}">
                            <input type="text" class="tw-flex-1 tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('options.'.$loop->index.'.text') tw-border-rose-500 @enderror" 
                                   name="options[{{ $loop->index }}][text]" 
                                   value="{{ old('options.'.$loop->index.'.text') }}"
                                   placeholder="Alternativa {{ $letter }}" required>
                            
                            <label class="option-correct tw-cursor-pointer tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg hover:tw-border-emerald-500 tw-transition-colors {{ old('correct_option') == $letter ? 'tw-bg-emerald-50 tw-border-emerald-500 tw-text-emerald-700' : 'tw-bg-white' }}" onclick="selectCorrect('{{ $letter }}')">
                                <input type="radio" name="correct_option" value="{{ $letter }}" {{ old('correct_option') == $letter ? 'checked' : '' }} class="tw-hidden">
                                <span class="tw-text-sm tw-font-medium"><i class="fas fa-check tw-mr-1"></i> Correta</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                
                <button type="button" class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors" onclick="addOption()">
                    <i class="fas fa-plus tw-mr-2"></i> Adicionar Alternativa E
                </button>
            </div>

            <!-- Explanation -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-lightbulb tw-text-brand-500"></i> Explicação
                </h2>
                
                <div class="tw-mb-4">
                    <label for="explanation" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Explicação da Resposta (opcional)</label>
                    <textarea class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" 
                              id="explanation" name="explanation" rows="4"
                              placeholder="Explique por que a alternativa correta está certa...">{{ old('explanation') }}</textarea>
                    <p class="tw-text-xs tw-text-slate-500 tw-mt-1">Aparecerá após o visitante responder a questão.</p>
                </div>
            </div>

            <!-- Tags -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-tags tw-text-brand-500"></i> Tags
                </h2>
                
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-3">Selecione as tags (opcional)</label>
                    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-3">
                        @foreach($tags as $tag)
                            <label class="tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                                       class="tw-rounded tw-border-slate-300 tw-text-brand-600 focus:tw-ring-brand-500"
                                       {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                <span class="tw-ml-2 tw-text-sm tw-text-slate-700">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($tags->isEmpty())
                        <p class="tw-text-slate-500 tw-text-sm">Nenhuma tag criada ainda. <a href="{{ route('admin.questions.tags') }}" class="tw-text-brand-600 hover:tw-underline">Criar tags</a></p>
                    @endif
                </div>
            </div>

            <!-- Submit -->
            <div class="tw-flex tw-justify-between tw-items-center tw-pt-4">
                <a href="{{ route('admin.questions.index') }}" class="tw-text-slate-600 hover:tw-text-slate-800 tw-font-medium">Cancelar</a>
                <div class="tw-flex tw-gap-3">
                    <button type="submit" name="redirect_to_create" value="1" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-brand-600 tw-text-brand-600 tw-font-medium tw-rounded-lg hover:tw-bg-brand-50 tw-transition-colors">
                        <i class="fas fa-plus tw-mr-2"></i> Salvar e Criar Outra
                    </button>
                    <button type="submit" class="tw-inline-flex tw-items-center tw-px-6 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-bold tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                        <i class="fas fa-check tw-mr-2"></i> Salvar Pergunta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
let optionCount = 4;
const letters = ['A', 'B', 'C', 'D', 'E', 'F'];

function selectCorrect(letter) {
    // Remove selected from all
    document.querySelectorAll('.option-correct').forEach(el => {
        el.classList.remove('tw-bg-emerald-50', 'tw-border-emerald-500', 'tw-text-emerald-700');
        el.classList.add('tw-bg-white');
    });
    document.querySelectorAll('.option-letter').forEach(el => {
        el.classList.remove('tw-bg-emerald-100', 'tw-text-emerald-800');
        el.classList.add('tw-bg-slate-100', 'tw-text-slate-600');
    });
    
    // Add selected to clicked
    const target = event.currentTarget;
    target.classList.remove('tw-bg-white');
    target.classList.add('tw-bg-emerald-50', 'tw-border-emerald-500', 'tw-text-emerald-700');
    
    // Update letter style
    const letterEl = document.getElementById('letter-' + letter);
    if(letterEl) {
        letterEl.classList.remove('tw-bg-slate-100', 'tw-text-slate-600');
        letterEl.classList.add('tw-bg-emerald-100', 'tw-text-emerald-800');
    }
}

function addOption() {
    if (optionCount >= 6) {
        alert('Máximo de 6 alternativas');
        return;
    }
    
    const letter = letters[optionCount];
    const container = document.getElementById('optionsContainer');
    
    const row = document.createElement('div');
    row.className = 'option-row tw-flex tw-items-center tw-gap-3';
    row.innerHTML = `
        <span class="option-letter tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-slate-100 tw-font-bold tw-text-slate-600 tw-flex-shrink-0" id="letter-${letter}">${letter}</span>
        <input type="hidden" name="options[${optionCount}][letter]" value="${letter}">
        <input type="text" class="tw-flex-1 tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" name="options[${optionCount}][text]" placeholder="Alternativa ${letter}" required>
        <label class="option-correct tw-cursor-pointer tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg hover:tw-border-emerald-500 tw-transition-colors tw-bg-white" onclick="selectCorrect('${letter}')">
            <input type="radio" name="correct_option" value="${letter}" style="display: none;">
            <span class="tw-text-sm tw-font-medium"><i class="fas fa-check tw-mr-1"></i> Correta</span>
        </label>
        <button type="button" class="tw-p-2 tw-text-rose-500 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors" onclick="removeOption(this, '${letter}')">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(row);
    optionCount++;
    
    // Update button text
    const btn = document.querySelector('[onclick="addOption()"]');
    if (optionCount >= 6) {
        btn.style.display = 'none';
    } else {
        btn.innerHTML = '<i class="fas fa-plus tw-mr-2"></i> Adicionar Alternativa ' + letters[optionCount];
    }
}

function removeOption(button, letter) {
    button.closest('.option-row').remove();
    optionCount--;
    
    // Update button
    const btn = document.querySelector('[onclick="addOption()"]');
    btn.style.display = 'inline-flex';
    btn.innerHTML = '<i class="fas fa-plus tw-mr-2"></i> Adicionar Alternativa ' + letters[optionCount];
}
</script>
@endsection
@endsection
