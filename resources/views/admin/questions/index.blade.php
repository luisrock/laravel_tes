@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <!-- Header -->
    <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-8 tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Banco de Perguntas</h1>
            <p class="tw-text-slate-500">Gerencie todas as perguntas disponíveis para os quizzes</p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <a href="{{ route('admin.questions.tags') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fas fa-tags tw-mr-2"></i> Tags
            </a>
            <a href="{{ route('admin.quizzes.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fas fa-brain tw-mr-2"></i> Quizzes
            </a>
            <a href="{{ route('admin.questions.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                <i class="fas fa-plus tw-mr-2"></i> Nova Pergunta
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="tw-bg-emerald-50 tw-text-emerald-700 tw-p-4 tw-rounded-lg tw-mb-6 tw-border tw-border-emerald-200 tw-flex tw-justify-between tw-items-center">
            <div><i class="fas fa-check-circle tw-mr-2"></i> {{ session('success') }}</div>
            <button onclick="this.parentElement.remove()" class="tw-text-emerald-500 hover:tw-text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if (session('error'))
        <div class="tw-bg-rose-50 tw-text-rose-700 tw-p-4 tw-rounded-lg tw-mb-6 tw-border tw-border-rose-200 tw-flex tw-justify-between tw-items-center">
            <div><i class="fas fa-exclamation-circle tw-mr-2"></i> {{ session('error') }}</div>
            <button onclick="this.parentElement.remove()" class="tw-text-rose-500 hover:tw-text-rose-700"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-8">
        <form method="GET" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-12 tw-gap-4 tw-items-end">
            <div class="md:tw-col-span-3">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Buscar</label>
                <input type="text" name="search" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" placeholder="Buscar no enunciado..." value="{{ request('search') }}">
            </div>
            <div class="md:tw-col-span-2">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Categoria</label>
                <select name="category_id" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:tw-col-span-2">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Dificuldade</label>
                <select name="difficulty" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500">
                    <option value="">Todas</option>
                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Fácil</option>
                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Intermediário</option>
                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Difícil</option>
                </select>
            </div>
            <div class="md:tw-col-span-2">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Tag</label>
                <select name="tag_id" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500">
                    <option value="">Todas</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:tw-col-span-1">
                <label class="tw-inline-flex tw-items-center tw-cursor-pointer tw-mt-6">
                    <input type="checkbox" name="unused" value="1" class="tw-sr-only tw-peer" {{ request('unused') ? 'checked' : '' }}>
                    <div class="tw-relative tw-w-9 tw-h-5 tw-bg-slate-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-brand-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full rtl:peer-checked:after:tw--translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-start-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-4 after:tw-w-4 after:tw-transition-all peer-checked:tw-bg-brand-600"></div>
                    <span class="tw-ms-2 tw-text-xs tw-font-medium tw-text-slate-700">Não usadas</span>
                </label>
            </div>
            <div class="md:tw-col-span-2">
                <button type="submit" class="tw-w-full tw-bg-slate-800 tw-text-white tw-font-medium tw-py-2 tw-rounded-lg hover:tw-bg-slate-900 tw-transition-colors">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Questions List -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-flex tw-justify-between tw-items-center">
            <span class="tw-font-medium tw-text-slate-700">{{ $questions->total() }} pergunta(s) encontrada(s)</span>
        </div>
        
        <div class="tw-p-6 tw-space-y-4">
            @forelse($questions as $question)
                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-slate-200 tw-p-6 tw-transition-all hover:tw-shadow-md tw-flex tw-flex-col md:tw-flex-row md:tw-items-start tw-justify-between tw-gap-4" 
                     style="border-left: 4px solid {{ $question->quizzes_count == 0 ? '#f59e0b' : '#3b82f6' }};">
                    
                    <div class="tw-flex-1">
                        <p class="tw-text-slate-800 tw-font-medium tw-mb-3">{{ Str::limit($question->text, 200) }}</p>
                        
                        <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-3">
                            @foreach($question->options as $option)
                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded tw-text-xs tw-font-medium {{ $option->is_correct ? 'tw-bg-emerald-100 tw-text-emerald-800' : 'tw-bg-slate-100 tw-text-slate-600' }}">
                                    {{ $option->letter }}
                                    @if($option->is_correct)
                                        <i class="fas fa-check tw-ml-1"></i>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                        
                        <div class="tw-flex tw-flex-wrap tw-gap-2">
                            @if($question->category)
                                <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-cyan-50 tw-text-cyan-700 tw-text-xs tw-font-semibold">{{ $question->category->name }}</span>
                            @endif
                            
                            @php
                                $diffColors = [
                                    'easy' => 'tw-text-emerald-600 tw-bg-emerald-50',
                                    'medium' => 'tw-text-amber-600 tw-bg-amber-50',
                                    'hard' => 'tw-text-rose-600 tw-bg-rose-50'
                                ];
                                $diffColor = $diffColors[$question->difficulty] ?? 'tw-text-slate-600 tw-bg-slate-50';
                            @endphp
                            <span class="tw-px-2 tw-py-1 tw-rounded {{ $diffColor }} tw-text-xs tw-font-semibold">
                                {{ $question->difficulty_label }}
                            </span>
                            
                            @foreach($question->tags as $tag)
                                <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-100 tw-text-slate-600 tw-text-xs tw-font-semibold">
                                    <i class="fas fa-tag tw-mr-1 tw-text-slate-400"></i> {{ $tag->name }}
                                </span>
                            @endforeach
                            
                            <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-50 tw-text-slate-600 tw-text-xs tw-font-semibold">
                                Em {{ $question->quizzes_count }} quiz(zes)
                            </span>
                            
                            @if($question->times_answered > 0)
                                <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-50 tw-text-slate-600 tw-text-xs tw-font-semibold">
                                    {{ $question->success_rate }}% de acerto
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="tw-flex tw-items-center tw-gap-2 tw-self-start">
                        <a href="{{ route('admin.questions.edit', $question) }}" class="tw-p-2 tw-text-blue-600 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors" title="Editar">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a href="{{ route('admin.questions.duplicate', $question) }}" class="tw-p-2 tw-text-cyan-600 hover:tw-bg-cyan-50 tw-rounded-lg tw-transition-colors" title="Duplicar">
                            <i class="fas fa-copy"></i>
                        </a>
                        <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="tw-inline-block" onsubmit="return confirm('Tem certeza?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tw-p-2 tw-text-rose-600 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors disabled:tw-opacity-50 disabled:tw-cursor-not-allowed" 
                                    title="Excluir" {{ $question->quizzes_count > 0 ? 'disabled' : '' }}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="tw-text-center tw-py-12">
                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-full tw-bg-slate-100 tw-mb-4">
                        <i class="fas fa-list tw-text-2xl tw-text-slate-400"></i>
                    </div>
                    <p class="tw-text-slate-500 tw-mb-4">Nenhuma pergunta encontrada.</p>
                    <a href="{{ route('admin.questions.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                        <i class="fas fa-plus tw-mr-2"></i> Criar Primeira Pergunta
                    </a>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="tw-px-6 tw-py-4 tw-border-t tw-border-slate-100 tw-flex tw-justify-center">
             {{ $questions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
