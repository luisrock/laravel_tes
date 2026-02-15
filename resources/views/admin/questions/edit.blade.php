@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <div class="tw-max-w-4xl tw-mx-auto">
        <!-- Header -->
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-8 tw-gap-4">
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Editar Pergunta</h1>
                <p class="tw-text-slate-500">ID: {{ $question->id }}</p>
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

        <!-- Usage Info -->
        @if($question->quizzes->count() > 0)
            <div class="tw-bg-cyan-50 tw-text-cyan-800 tw-p-4 tw-rounded-lg tw-mb-6 tw-border tw-border-cyan-200">
                <strong class="tw-block tw-mb-2">Esta pergunta está em {{ $question->quizzes->count() }} quiz(zes):</strong>
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    @foreach($question->quizzes as $quiz)
                        <a href="{{ route('admin.quizzes.questions', $quiz) }}" class="tw-inline-block tw-px-3 tw-py-1 tw-bg-white tw-border tw-border-cyan-200 tw-rounded-full tw-text-sm tw-font-medium hover:tw-bg-cyan-100 tw-transition-colors">{{ $quiz->title }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.questions.update', $question) }}" id="questionForm">
            @csrf
            @method('PUT')

            <!-- Question Text -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-question-circle tw-text-brand-500"></i> Enunciado
                </h2>
                
                <div class="tw-mb-4">
                    <label for="text" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Texto da Pergunta <span class="tw-text-rose-500">*</span></label>
                    <textarea class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('text') tw-border-rose-500 @enderror" 
                              id="text" name="text" rows="4" required>{{ old('text', $question->text) }}</textarea>
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
                                <option value="{{ $category->id }}" {{ old('category_id', $question->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="difficulty" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Dificuldade <span class="tw-text-rose-500">*</span></label>
                        <select class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" id="difficulty" name="difficulty" required>
                            <option value="easy" {{ old('difficulty', $question->difficulty) == 'easy' ? 'selected' : '' }}>Fácil</option>
                            <option value="medium" {{ old('difficulty', $question->difficulty) == 'medium' ? 'selected' : '' }}>Intermediário</option>
                            <option value="hard" {{ old('difficulty', $question->difficulty) == 'hard' ? 'selected' : '' }}>Difícil</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-2 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-list tw-text-brand-500"></i> Alternativas
                </h2>
                
                <div id="optionsContainer" class="tw-space-y-4">
                    @php $correctOption = $question->correctOption->letter ?? null; @endphp
                    @foreach($question->options as $index => $option)
                        <div class="option-row tw-flex tw-items-center tw-gap-3">
                            <span class="option-letter tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-font-bold tw-flex-shrink-0 {{ $option->is_correct ? 'tw-bg-emerald-100 tw-text-emerald-800' : 'tw-bg-slate-100 tw-text-slate-600' }}" id="letter-{{ $option->letter }}">{{ $option->letter }}</span>
                            <input type="hidden" name="options[{{ $index }}][letter]" value="{{ $option->letter }}">
                            <input type="text" class="tw-flex-1 tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" 
                                   name="options[{{ $index }}][text]" 
                                   value="{{ old('options.'.$index.'.text', $option->text) }}" required>
                            
                            <label class="option-correct tw-cursor-pointer tw-px-3 tw-py-2 tw-border tw-border-slate-200 tw-rounded-lg hover:tw-border-emerald-500 tw-transition-colors {{ $option->is_correct ? 'tw-bg-emerald-50 tw-border-emerald-500 tw-text-emerald-700' : 'tw-bg-white' }}" onclick="selectCorrect('{{ $option->letter }}')">
                                <input type="radio" name="correct_option" value="{{ $option->letter }}" {{ $option->is_correct ? 'checked' : '' }} class="tw-hidden">
                                <span class="tw-text-sm tw-font-medium"><i class="fas fa-check tw-mr-1"></i> Correta</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Explanation -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-lightbulb tw-text-brand-500"></i> Explicação
                </h2>
                
                <div class="tw-mb-4">
                    <label for="explanation" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Explicação da Resposta</label>
                    <textarea class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" 
                              id="explanation" name="explanation" rows="4">{{ old('explanation', $question->explanation) }}</textarea>
                </div>
            </div>

            <!-- Tags -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-tags tw-text-brand-500"></i> Tags
                </h2>
                
                <div class="tw-mb-4">
                    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-3">
                        @php $questionTagIds = $question->tags->pluck('id')->toArray(); @endphp
                        @foreach($tags as $tag)
                            <label class="tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                                       class="tw-rounded tw-border-slate-300 tw-text-brand-600 focus:tw-ring-brand-500"
                                       {{ in_array($tag->id, old('tags', $questionTagIds)) ? 'checked' : '' }}>
                                <span class="tw-ml-2 tw-text-sm tw-text-slate-700">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Stats -->
            @if($question->times_answered > 0)
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                    <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                        <i class="fas fa-chart-bar tw-text-brand-500"></i> Estatísticas
                    </h2>
                    <div class="tw-grid tw-grid-cols-3 tw-gap-4">
                        <div class="tw-text-center tw-p-4 tw-bg-slate-50 tw-rounded-lg">
                            <h3 class="tw-text-2xl tw-font-bold tw-text-slate-800">{{ $question->times_answered }}</h3>
                            <p class="tw-text-sm tw-text-slate-500">Vezes respondida</p>
                        </div>
                        <div class="tw-text-center tw-p-4 tw-bg-slate-50 tw-rounded-lg">
                            <h3 class="tw-text-2xl tw-font-bold tw-text-slate-800">{{ $question->times_correct }}</h3>
                            <p class="tw-text-sm tw-text-slate-500">Acertos</p>
                        </div>
                        <div class="tw-text-center tw-p-4 tw-bg-slate-50 tw-rounded-lg">
                            <h3 class="tw-text-2xl tw-font-bold tw-text-brand-600">{{ $question->success_rate }}%</h3>
                            <p class="tw-text-sm tw-text-slate-500">Taxa de acerto</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit -->
            <div class="tw-flex tw-justify-between tw-items-center tw-pt-4">
                <a href="{{ route('admin.questions.index') }}" class="tw-text-slate-600 hover:tw-text-slate-800 tw-font-medium">Cancelar</a>
                <button type="submit" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-brand-600 tw-text-white tw-font-bold tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                    <i class="fas fa-check tw-mr-2"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
function selectCorrect(letter) {
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
</script>
@endsection
@endsection
