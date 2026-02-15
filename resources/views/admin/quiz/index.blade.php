@extends('layouts.admin')

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-5xl">
    <!-- Header -->
    <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-center tw-mb-8 tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-800">Quizzes</h1>
            <p class="tw-text-slate-500">Gerencie os quizzes do site</p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <!-- Toggle Home Visibility -->
            <form action="{{ route('admin.quizzes.toggle-home') }}" method="POST">
                @csrf
                @if($isVisibleOnHome)
                    <button type="submit" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-emerald-100 tw-text-emerald-700 tw-font-medium tw-rounded-lg hover:tw-bg-emerald-200 tw-transition-colors" title="Clique para ocultar da home">
                        <i class="fas fa-eye tw-mr-2"></i> Visível na Home
                    </button>
                @else
                    <button type="submit" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-slate-100 tw-text-slate-600 tw-font-medium tw-rounded-lg hover:tw-bg-slate-200 tw-transition-colors" title="Clique para publicar na home">
                        <i class="fas fa-eye-slash tw-mr-2"></i> Oculto na Home
                    </button>
                @endif
            </form>
            
            <a href="{{ route('admin.questions.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 tw-font-medium tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors">
                <i class="fas fa-list tw-mr-2"></i> Banco de Perguntas
            </a>
            <a href="{{ route('admin.quizzes.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                <i class="fas fa-plus tw-mr-2"></i> Novo Quiz
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

    <!-- Filters -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6 tw-mb-8">
        <form method="GET" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-12 tw-gap-4 tw-items-end">
            <div class="md:tw-col-span-3">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Buscar</label>
                <input type="text" name="search" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500" placeholder="Título ou descrição..." value="{{ request('search') }}">
            </div>
            <div class="md:tw-col-span-2">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Status</label>
                <select name="status" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500">
                    <option value="">Todos</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Arquivado</option>
                </select>
            </div>
            <div class="md:tw-col-span-2">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Tribunal</label>
                <select name="tribunal" class="tw-w-full tw-rounded-lg tw-border-slate-300 focus:tw-border-brand-500 focus:tw-ring-brand-500">
                    <option value="">Todos</option>
                    <option value="STF" {{ request('tribunal') == 'STF' ? 'selected' : '' }}>STF</option>
                    <option value="STJ" {{ request('tribunal') == 'STJ' ? 'selected' : '' }}>STJ</option>
                    <option value="TST" {{ request('tribunal') == 'TST' ? 'selected' : '' }}>TST</option>
                    <option value="TNU" {{ request('tribunal') == 'TNU' ? 'selected' : '' }}>TNU</option>
                </select>
            </div>
            <div class="md:tw-col-span-3">
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
                <button type="submit" class="tw-w-full tw-bg-slate-800 tw-text-white tw-font-medium tw-py-2 tw-rounded-lg hover:tw-bg-slate-900 tw-transition-colors">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Quiz List -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-flex tw-justify-between tw-items-center">
            <span class="tw-font-medium tw-text-slate-700">{{ $quizzes->total() }} quiz(zes) encontrado(s)</span>
        </div>
        
        <div class="tw-p-6 tw-space-y-4">
            @forelse($quizzes as $quiz)
                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-slate-200 tw-p-6 tw-transition-all hover:tw-shadow-md tw-flex tw-flex-col md:tw-flex-row md:tw-items-start tw-justify-between tw-gap-4" 
                     style="border-left: 4px solid {{ $quiz->color ?? '#cbd5e1' }};">
                    
                    <div class="tw-flex-1">
                        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-3 tw-mb-2">
                            <h3 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-0">{{ $quiz->title }}</h3>
                            
                            @php
                                $statusColors = [
                                    'draft' => 'tw-bg-amber-100 tw-text-amber-800',
                                    'published' => 'tw-bg-emerald-100 tw-text-emerald-800',
                                    'archived' => 'tw-bg-slate-100 tw-text-slate-800'
                                ];
                                $statusClass = $statusColors[$quiz->status] ?? 'tw-bg-slate-100 tw-text-slate-800';
                            @endphp
                            <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-bold tw-uppercase {{ $statusClass }}">
                                {{ $quiz->status_label }}
                            </span>
                        </div>
                        
                        <p class="tw-text-slate-500 tw-mb-4 tw-text-sm">{{ Str::limit($quiz->description, 150) }}</p>
                        
                        <div class="tw-flex tw-flex-wrap tw-gap-2">
                            @if($quiz->tribunal)
                                <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-blue-50 tw-text-blue-700 tw-text-xs tw-font-semibold">{{ $quiz->tribunal }}</span>
                            @endif
                            @if($quiz->tema_number)
                                <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-100 tw-text-slate-600 tw-text-xs tw-font-semibold">Tema {{ $quiz->tema_number }}</span>
                            @endif
                            @if($quiz->category)
                                <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-purple-50 tw-text-purple-700 tw-text-xs tw-font-semibold">{{ $quiz->category->name }}</span>
                            @endif
                            
                            @php
                                $diffColors = [
                                    'easy' => 'tw-text-emerald-600',
                                    'medium' => 'tw-text-amber-600',
                                    'hard' => 'tw-text-rose-600'
                                ];
                                $diffColor = $diffColors[$quiz->difficulty] ?? 'tw-text-slate-600';
                            @endphp
                            <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-50 {{ $diffColor }} tw-text-xs tw-font-semibold">
                                {{ $quiz->difficulty_label }}
                            </span>
                            
                            <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-50 tw-text-slate-600 tw-text-xs tw-font-semibold">
                                {{ $quiz->questions_count }} pergunta(s)
                            </span>
                            <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-50 tw-text-slate-600 tw-text-xs tw-font-semibold">
                                ~{{ $quiz->estimated_time }} min
                            </span>
                            <span class="tw-px-2 tw-py-1 tw-rounded tw-bg-slate-50 tw-text-slate-600 tw-text-xs tw-font-semibold">
                                {{ $quiz->views_count }} views
                            </span>
                        </div>
                    </div>
                    
                    <div class="tw-flex tw-items-center tw-gap-2 tw-self-start">
                        <a href="{{ route('admin.quizzes.questions', $quiz) }}" class="tw-p-2 tw-text-blue-600 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors" title="Gerenciar Perguntas">
                            <i class="fas fa-list-ol"></i>
                        </a>
                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="tw-p-2 tw-text-slate-600 hover:tw-bg-slate-100 tw-rounded-lg tw-transition-colors" title="Editar">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a href="{{ route('admin.quizzes.duplicate', $quiz) }}" class="tw-p-2 tw-text-cyan-600 hover:tw-bg-cyan-50 tw-rounded-lg tw-transition-colors" title="Duplicar">
                            <i class="fas fa-copy"></i>
                        </a>
                        @if($quiz->status == 'published')
                            <a href="{{ route('quiz.show', $quiz->slug) }}" class="tw-p-2 tw-text-emerald-600 hover:tw-bg-emerald-50 tw-rounded-lg tw-transition-colors" target="_blank" title="Ver no site">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @endif
                        <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="tw-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este quiz?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tw-p-2 tw-text-rose-600 hover:tw-bg-rose-50 tw-rounded-lg tw-transition-colors" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="tw-text-center tw-py-12">
                    <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-full tw-bg-slate-100 tw-mb-4">
                        <i class="fas fa-brain tw-text-2xl tw-text-slate-400"></i>
                    </div>
                    <p class="tw-text-slate-500 tw-mb-4">Nenhum quiz encontrado.</p>
                    <a href="{{ route('admin.quizzes.create') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                        <i class="fas fa-plus tw-mr-2"></i> Criar Primeiro Quiz
                    </a>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="tw-px-6 tw-py-4 tw-border-t tw-border-slate-100 tw-flex tw-justify-center">
             {{ $quizzes->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
