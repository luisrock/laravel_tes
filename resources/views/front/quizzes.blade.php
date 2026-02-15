@extends('front.base')

@section('page-title', isset($category) ? 'Quizzes de ' . $category->name : 'Quizzes Jurídicos')

@section('content')
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-7xl">
        
        <!-- Breadcrumbs -->
        <nav class="tw-flex tw-text-sm tw-text-slate-500 tw-mb-8" aria-label="Breadcrumb">
            <ol class="tw-inline-flex tw-items-center tw-space-x-1 md:tw-space-x-3">
                <li class="tw-inline-flex tw-items-center">
                    <a href="{{ url('/') }}" class="tw-inline-flex tw-items-center tw-text-slate-500 hover:tw-text-brand-600">
                        <i class="fa fa-home tw-mr-2"></i> Início
                    </a>
                </li>
                <li>
                    <div class="tw-flex tw-items-center">
                        <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                        @if(isset($category))
                            <a href="{{ route('quizzes.index') }}" class="tw-text-slate-500 hover:tw-text-brand-600">Quizzes</a>
                            <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                            <span class="tw-text-slate-700 tw-font-medium">{{ $category->name }}</span>
                        @else
                            <span class="tw-text-slate-700 tw-font-medium">Quizzes Jurídicos</span>
                        @endif
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="tw-mb-8">
            <h1 class="tw-text-3xl tw-font-bold tw-text-slate-900 tw-mb-2">
                @if(isset($category))
                    Quizzes de {{ $category->name }}
                @else
                    Quizzes Jurídicos
                @endif
            </h1>
            <p class="tw-text-slate-600 tw-text-lg tw-max-w-3xl">
                Teste seus conhecimentos sobre jurisprudência vinculante do STF e STJ. 
                Cada quiz possui questões baseadas em teses de repercussão geral e recursos repetitivos.
            </p>
        </div>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-4 tw-gap-8">
            
            <!-- Sidebar Filters -->
            <aside class="tw-space-y-6">
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-p-5 tw-border-b tw-border-slate-100">
                        <h3 class="tw-font-semibold tw-text-slate-800 tw-flex tw-items-center tw-gap-2">
                            <i class="fa fa-filter tw-text-brand-500"></i> Filtros
                        </h3>
                    </div>
                    
                    <div class="tw-p-5 tw-space-y-6">
                        <!-- Category Filter -->
                        <div>
                            <h4 class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-3">Categoria</h4>
                            <ul class="tw-space-y-1">
                                <li>
                                    <a href="{{ route('quizzes.index') }}" 
                                       class="tw-flex tw-items-center tw-justify-between tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-transition-colors {{ !isset($category) && !request('tribunal') && !request('dificuldade') ? 'tw-bg-brand-50 tw-text-brand-700 tw-font-medium' : 'tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600' }}">
                                        <span>Todas as categorias</span>
                                    </a>
                                </li>
                                @foreach($categories as $cat)
                                    @if($cat->quizzes_count > 0)
                                    <li>
                                        <a href="{{ route('quizzes.category', $cat->slug) }}" 
                                           class="tw-flex tw-items-center tw-justify-between tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-transition-colors {{ isset($category) && $category->id == $cat->id ? 'tw-bg-brand-50 tw-text-brand-700 tw-font-medium' : 'tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600' }}">
                                            <span>{{ $cat->name }}</span>
                                            <span class="tw-bg-slate-100 tw-text-slate-500 tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium group-hover:tw-bg-brand-100 group-hover:tw-text-brand-600">{{ $cat->quizzes_count }}</span>
                                        </a>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <!-- Tribunal Filter -->
                        <div>
                            <h4 class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-3">Tribunal</h4>
                            <ul class="tw-space-y-1">
                                @foreach(['STF', 'STJ', 'TST', 'TNU'] as $tribunal)
                                <li>
                                    <a href="{{ route('quizzes.index', array_merge(request()->except('tribunal'), ['tribunal' => $tribunal])) }}" 
                                       class="tw-flex tw-items-center tw-justify-between tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-transition-colors {{ request('tribunal') == $tribunal ? 'tw-bg-brand-50 tw-text-brand-700 tw-font-medium' : 'tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600' }}">
                                        <span>{{ $tribunal }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Difficulty Filter -->
                        <div>
                            <h4 class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-3">Dificuldade</h4>
                            <ul class="tw-space-y-1">
                                <li>
                                    <a href="{{ route('quizzes.index', array_merge(request()->except('dificuldade'), ['dificuldade' => 'easy'])) }}" 
                                       class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-transition-colors {{ request('dificuldade') == 'easy' ? 'tw-bg-brand-50 tw-text-brand-700 tw-font-medium' : 'tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600' }}">
                                        <span class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-emerald-500"></span>
                                        <span>Fácil</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('quizzes.index', array_merge(request()->except('dificuldade'), ['dificuldade' => 'medium'])) }}" 
                                       class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-transition-colors {{ request('dificuldade') == 'medium' ? 'tw-bg-brand-50 tw-text-brand-700 tw-font-medium' : 'tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600' }}">
                                        <span class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-amber-500"></span>
                                        <span>Intermediário</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('quizzes.index', array_merge(request()->except('dificuldade'), ['dificuldade' => 'hard'])) }}" 
                                       class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-rounded-lg tw-text-sm tw-transition-colors {{ request('dificuldade') == 'hard' ? 'tw-bg-brand-50 tw-text-brand-700 tw-font-medium' : 'tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600' }}">
                                        <span class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-rose-500"></span>
                                        <span>Difícil</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    @if(request()->hasAny(['tribunal', 'dificuldade']) || isset($category))
                    <div class="tw-p-4 tw-bg-slate-50 tw-border-t tw-border-slate-100">
                        <a href="{{ route('quizzes.index') }}" class="tw-block tw-w-full tw-py-2 tw-px-4 tw-bg-white tw-border tw-border-slate-300 tw-rounded-lg tw-text-slate-700 tw-text-sm tw-font-medium tw-text-center hover:tw-bg-slate-50 hover:tw-text-slate-900 tw-transition-colors">
                            <i class="fa fa-times tw-mr-1"></i> Limpar Filtros
                        </a>
                    </div>
                    @endif
                </div>
            </aside>

            <!-- Quiz Grid -->
            <main class="lg:tw-col-span-3">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                    <h2 class="tw-text-xl tw-font-bold tw-text-slate-800">
                        @if(isset($category))
                            Quizzes de {{ $category->name }}
                        @elseif(request('tribunal'))
                            Quizzes do {{ request('tribunal') }}
                        @elseif(request('dificuldade'))
                            Quizzes - Nível {{ request('dificuldade') == 'easy' ? 'Fácil' : (request('dificuldade') == 'medium' ? 'Intermediário' : 'Difícil') }}
                        @else
                            Todos os Quizzes
                        @endif
                    </h2>
                    <span class="tw-text-sm tw-font-medium tw-text-slate-500 tw-bg-white tw-px-3 tw-py-1 tw-rounded-full tw-border tw-border-slate-200">
                        {{ $quizzes->total() }} quiz{{ $quizzes->total() != 1 ? 'zes' : '' }}
                    </span>
                </div>

                @if($quizzes->count() > 0)
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-2 xl:tw-grid-cols-3 tw-gap-6">
                        @foreach($quizzes as $quiz)
                            <article class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden tw-flex tw-flex-col tw-h-full hover:tw-shadow-md hover:tw-border-brand-200 tw-transition-all tw-duration-300 group">
                                <div class="tw-px-6 tw-pt-6 tw-pb-4 tw-bg-brand-600 tw-bg-gradient-to-br tw-from-brand-600 tw-to-brand-700 tw-text-white tw-relative tw-overflow-hidden">
                                    <div class="tw-absolute tw-top-0 tw-right-0 tw-p-8 tw-opacity-10 tw-transform tw-translate-x-1/4 tw--translate-y-1/4">
                                        <i class="fa fa-graduation-cap tw-text-9xl"></i>
                                    </div>
                                    
                                    @if($quiz->category)
                                        <span class="tw-inline-block tw-bg-white/20 tw-backdrop-blur-sm tw-px-2.5 tw-py-1 tw-rounded tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-mb-3">
                                            {{ $quiz->category->name }}
                                        </span>
                                    @endif
                                    
                                    <h3 class="tw-text-lg tw-font-bold tw-leading-snug tw-mb-1 tw-relative tw-z-10 group-hover:tw-underline">
                                        <a href="{{ route('quiz.show', $quiz->slug) }}" class="focus:tw-outline-none">
                                            <span class="tw-absolute tw-inset-0"></span>
                                            {{ $quiz->title }}
                                        </a>
                                    </h3>
                                </div>
                                
                                <div class="tw-p-6 tw-flex-1 tw-flex tw-flex-col">
                                    <p class="tw-text-slate-600 tw-text-sm tw-leading-relaxed tw-mb-6 tw-flex-1">
                                        {{ Str::limit($quiz->description, 120) }}
                                    </p>
                                    
                                    <div class="tw-flex tw-flex-wrap tw-gap-3 tw-text-xs tw-font-medium tw-text-slate-500 tw-pt-4 tw-border-t tw-border-slate-100">
                                        @if($quiz->tribunal)
                                            <span class="tw-flex tw-items-center tw-gap-1">
                                                <i class="fa fa-gavel tw-text-slate-400"></i> {{ $quiz->tribunal }}
                                            </span>
                                        @endif
                                        <span class="tw-flex tw-items-center tw-gap-1">
                                            <i class="fa fa-list-ol tw-text-slate-400"></i> {{ $quiz->questions_count }} questões
                                        </span>
                                        <span class="tw-flex tw-items-center tw-gap-1">
                                            <i class="fa fa-clock-o tw-text-slate-400"></i> ~{{ $quiz->estimated_time }} min
                                        </span>
                                        
                                        @php
                                            $diffColor = match($quiz->difficulty) {
                                                'easy' => 'tw-text-emerald-600',
                                                'medium' => 'tw-text-amber-600',
                                                'hard' => 'tw-text-rose-600',
                                                default => 'tw-text-slate-600'
                                            };
                                        @endphp
                                        <span class="tw-flex tw-items-center tw-gap-1 {{ $diffColor }}">
                                            <i class="fa fa-signal"></i> {{ $quiz->difficulty_label }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="tw-px-6 tw-pb-6">
                                    <a href="{{ route('quiz.show', $quiz->slug) }}" 
                                       class="tw-inline-flex tw-items-center tw-justify-center tw-w-full tw-px-4 tw-py-2.5 tw-bg-slate-50 tw-text-brand-700 tw-font-semibold tw-rounded-lg tw-border tw-border-slate-200 hover:tw-bg-brand-600 hover:tw-text-white hover:tw-border-brand-600 tw-transition-colors group-hover:tw-bg-brand-50 group-hover:tw-text-brand-700">
                                        <i class="fa fa-play tw-mr-2 tw-text-xs"></i> Iniciar Quiz
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    @if($quizzes->hasPages())
                    <div class="tw-mt-8 tw-flex tw-justify-center">
                        {{ $quizzes->appends(request()->query())->links() }} 
                        <!-- Note: Pagination view might need update too, or used standard simple-tailwind -->
                    </div>
                    @endif
                @else
                    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-12 tw-text-center">
                        <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-20 tw-h-20 tw-rounded-full tw-bg-slate-100 tw-mb-6">
                            <i class="fa fa-search tw-text-3xl tw-text-slate-400"></i>
                        </div>
                        <h3 class="tw-text-lg tw-font-bold tw-text-slate-900 tw-mb-2">Nenhum quiz encontrado</h3>
                        <p class="tw-text-slate-500 tw-mb-8">Não encontramos quizzes com os filtros selecionados. Tente ajustar sua busca.</p>
                        <a href="{{ route('quizzes.index') }}" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-brand-600 tw-text-white tw-font-semibold tw-rounded-lg hover:tw-bg-brand-700 tw-transition-colors">
                            <i class="fa fa-refresh tw-mr-2"></i> Limpar Filtros
                        </a>
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection
