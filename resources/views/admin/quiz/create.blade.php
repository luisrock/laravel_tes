@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <div class="tw-max-w-4xl tw-mx-auto">
        <!-- Header -->
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-8">
            <div>
                <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Criar Novo Quiz</h1>
                <p class="tw-text-slate-500">Preencha as informações básicas. As perguntas serão adicionadas depois.</p>
            </div>
            <a href="{{ route('admin.quizzes.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fa fa-arrow-left tw-mr-2"></i> Voltar
            </a>
        </div>

        <form method="POST" action="{{ route('admin.quizzes.store') }}">
            @csrf

            <!-- Basic Info -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-info-circle tw-text-brand-500"></i> Informações Básicas
                </h2>
                
                <div class="tw-mb-4">
                    <label for="title" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Título do Quiz <span class="tw-text-rose-500">*</span></label>
                    <input type="text" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('title') tw-border-rose-500 @enderror" 
                           id="title" name="title" value="{{ old('title') }}" required
                           placeholder="Ex: Prescrição e Reconhecimento de Dívida">
                    @error('title')
                        <p class="tw-text-xs tw-text-rose-500 tw-mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-mb-4">
                    <div>
                        <label for="tribunal" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Tribunal</label>
                        <select class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('tribunal') tw-border-rose-500 @enderror" id="tribunal" name="tribunal">
                            <option value="">Selecione...</option>
                            <option value="STF" {{ old('tribunal') == 'STF' ? 'selected' : '' }}>STF</option>
                            <option value="STJ" {{ old('tribunal') == 'STJ' ? 'selected' : '' }}>STJ</option>
                            <option value="TST" {{ old('tribunal') == 'TST' ? 'selected' : '' }}>TST</option>
                            <option value="TNU" {{ old('tribunal') == 'TNU' ? 'selected' : '' }}>TNU</option>
                        </select>
                    </div>
                    <div>
                        <label for="tema_number" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Número do Tema (opcional)</label>
                        <input type="number" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('tema_number') tw-border-rose-500 @enderror" 
                               id="tema_number" name="tema_number" value="{{ old('tema_number') }}"
                               placeholder="Ex: 999">
                    </div>
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-mb-4">
                    <div>
                        <label for="category_id" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Categoria</label>
                        <select class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('category_id') tw-border-rose-500 @enderror" id="category_id" name="category_id">
                            <option value="">Selecione...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="estimated_time" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Tempo Estimado (minutos) <span class="tw-text-rose-500">*</span></label>
                        <input type="number" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('estimated_time') tw-border-rose-500 @enderror" 
                               id="estimated_time" name="estimated_time" value="{{ old('estimated_time', 2) }}" 
                               min="1" max="120" required>
                    </div>
                </div>

                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-2">Dificuldade <span class="tw-text-rose-500">*</span></label>
                    <div class="tw-inline-flex tw-rounded-lg tw-shadow-sm" role="group">
                        <label class="tw-relative tw-flex-1">
                            <input type="radio" name="difficulty" value="easy" class="tw-peer tw-sr-only" {{ old('difficulty', 'medium') == 'easy' ? 'checked' : '' }}>
                            <div class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-border tw-border-slate-200 tw-rounded-l-lg tw-cursor-pointer peer-checked:tw-bg-emerald-50 peer-checked:tw-text-emerald-700 peer-checked:tw-border-emerald-200 hover:tw-bg-slate-50 tw-transition-colors">
                                Fácil
                            </div>
                        </label>
                        <label class="tw-relative tw-flex-1">
                            <input type="radio" name="difficulty" value="medium" class="tw-peer tw-sr-only" {{ old('difficulty', 'medium') == 'medium' ? 'checked' : '' }}>
                            <div class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-border-t tw-border-b tw-border-slate-200 tw-cursor-pointer peer-checked:tw-bg-amber-50 peer-checked:tw-text-amber-700 peer-checked:tw-border-amber-200 hover:tw-bg-slate-50 tw-transition-colors">
                                Intermediário
                            </div>
                        </label>
                        <label class="tw-relative tw-flex-1">
                            <input type="radio" name="difficulty" value="hard" class="tw-peer tw-sr-only" {{ old('difficulty', 'medium') == 'hard' ? 'checked' : '' }}>
                            <div class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-border tw-border-slate-200 tw-rounded-r-lg tw-cursor-pointer peer-checked:tw-bg-rose-50 peer-checked:tw-text-rose-700 peer-checked:tw-border-rose-200 hover:tw-bg-slate-50 tw-transition-colors">
                                Difícil
                            </div>
                        </label>
                    </div>
                </div>

                <div class="tw-mb-4">
                    <label for="description" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Descrição / Meta Description</label>
                    <textarea class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('description') tw-border-rose-500 @enderror" 
                              id="description" name="description" rows="3"
                              placeholder="Descrição breve para SEO (150-160 caracteres recomendado)">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Color -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-palette tw-text-brand-500"></i> Cor do Quiz
                </h2>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-2">Selecione uma cor:</label>
                    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-4">
                        @foreach($colors as $color)
                            <div class="color-option tw-w-10 tw-h-10 tw-rounded-lg tw-cursor-pointer tw-border-2 tw-border-transparent hover:tw-scale-110 tw-transition-transform" 
                                 style="background-color: {{ $color }};" 
                                 data-color="{{ $color }}"
                                 onclick="selectColor('{{ $color }}')"></div>
                        @endforeach
                    </div>
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <label class="tw-text-sm tw-text-slate-600">Personalizada:</label>
                        <input type="color" id="customColor" value="{{ old('color', '#912F56') }}" 
                               class="tw-w-12 tw-h-10 tw-p-0 tw-border-0 tw-rounded tw-cursor-pointer"
                               onchange="selectColor(this.value)">
                        <input type="text" class="tw-w-24 tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" id="colorHex" name="color" 
                               value="{{ old('color', '#912F56') }}" 
                               pattern="^#[0-9A-Fa-f]{6}$" required>
                        <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-border tw-border-slate-200" id="colorPreview" style="background-color: {{ old('color', '#912F56') }};"></div>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-cog tw-text-brand-500"></i> Opções de Exibição
                </h2>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-x-8 tw-gap-y-4">
                    <label class="tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="show_ads" value="1" class="tw-sr-only tw-peer" {{ old('show_ads', true) ? 'checked' : '' }}>
                        <div class="tw-relative tw-w-11 tw-h-6 tw-bg-slate-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-brand-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw--translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-brand-600"></div>
                        <span class="tw-ms-3 tw-text-sm tw-font-medium tw-text-slate-700">Exibir Anúncios</span>
                    </label>

                    <label class="tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="show_share" value="1" class="tw-sr-only tw-peer" {{ old('show_share', true) ? 'checked' : '' }}>
                        <div class="tw-relative tw-w-11 tw-h-6 tw-bg-slate-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-brand-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw--translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-brand-600"></div>
                        <span class="tw-ms-3 tw-text-sm tw-font-medium tw-text-slate-700">Botões de Compartilhar</span>
                    </label>

                    <label class="tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="show_progress" value="1" class="tw-sr-only tw-peer" {{ old('show_progress', true) ? 'checked' : '' }}>
                        <div class="tw-relative tw-w-11 tw-h-6 tw-bg-slate-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-brand-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw--translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-brand-600"></div>
                        <span class="tw-ms-3 tw-text-sm tw-font-medium tw-text-slate-700">Barra de Progresso</span>
                    </label>

                    <label class="tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="random_order" value="1" class="tw-sr-only tw-peer" {{ old('random_order') ? 'checked' : '' }}>
                        <div class="tw-relative tw-w-11 tw-h-6 tw-bg-slate-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-brand-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw--translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-brand-600"></div>
                        <span class="tw-ms-3 tw-text-sm tw-font-medium tw-text-slate-700">Ordem Aleatória</span>
                    </label>

                    <label class="tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="show_feedback_immediately" value="1" class="tw-sr-only tw-peer" {{ old('show_feedback_immediately', true) ? 'checked' : '' }}>
                        <div class="tw-relative tw-w-11 tw-h-6 tw-bg-slate-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-brand-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw--translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-brand-600"></div>
                        <span class="tw-ms-3 tw-text-sm tw-font-medium tw-text-slate-700">Feedback Imediato</span>
                    </label>
                </div>
            </div>

            <!-- SEO -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-search tw-text-brand-500"></i> SEO
                </h2>
                
                <div class="tw-mb-4">
                    <label for="slug" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Slug da URL (gerado automaticamente se vazio)</label>
                    <div class="tw-flex rounded-md tw-shadow-sm">
                        <span class="tw-inline-flex tw-items-center tw-px-3 tw-rounded-l-lg tw-border tw-border-r-0 tw-border-slate-300 tw-bg-slate-50 tw-text-slate-500 tw-text-sm">
                            /quiz/
                        </span>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                               class="tw-flex-1 tw-rounded-none tw-rounded-r-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('slug') tw-border-rose-500 @enderror"
                               placeholder="prescricao-reconhecimento-divida">
                    </div>
                </div>

                <div class="tw-mb-4">
                    <label for="meta_keywords" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Palavras-chave (separadas por vírgula)</label>
                    <input type="text" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('meta_keywords') tw-border-rose-500 @enderror" 
                           id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}"
                           placeholder="quiz jurídico, teses STF, prescrição">
                </div>
            </div>

            <!-- Status -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-6">
                <h2 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
                    <i class="fas fa-flag tw-text-brand-500"></i> Status
                </h2>
                
                <div class="tw-mb-4">
                    <select class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('status') tw-border-rose-500 @enderror" id="status" name="status">
                        <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                    </select>
                    <p class="tw-text-xs tw-text-slate-500 tw-mt-1">Recomendamos criar como rascunho e publicar após adicionar as perguntas.</p>
                </div>
            </div>

            <!-- Submit -->
            <div class="tw-flex tw-justify-between tw-items-center tw-pt-4">
                <a href="{{ route('admin.quizzes.index') }}" class="tw-text-slate-600 hover:tw-text-slate-800 tw-font-medium">Cancelar</a>
                <button type="submit" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-brand-600 tw-text-white tw-font-bold tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                    <i class="fas fa-check tw-mr-2"></i> Criar Quiz e Adicionar Perguntas
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function selectColor(color) {
        // Remove selected from all
        document.querySelectorAll('.color-option').forEach(el => el.classList.remove('tw-ring-2', 'tw-ring-offset-2', 'tw-ring-brand-500'));
        
        // Find matching palette color & add standard CSS class for ring
        const paletteOption = document.querySelector(`.color-option[data-color="${color}"]`);
        if (paletteOption) {
            paletteOption.classList.add('tw-ring-2', 'tw-ring-offset-2', 'tw-ring-brand-500');
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
@endsection
