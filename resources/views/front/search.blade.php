@extends('front.base')

@section('page-title', 'Pesquisa')

@section('content')

<div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-py-8 md:tw-py-12 tw-space-y-8">

    <!-- Search Section -->
    @include('partials.search-form', ['keyword' => $keyword ?? '', 'lista_tribunais' => $lista_tribunais])
    <!-- END Search Section -->

    <!-- Precedentes Vinculantes CPC -->
    @if(!empty($precedentes_home ?? null))
    <section class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden hover:tw-shadow-md tw-transition-shadow">
        <header class="tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between tw-gap-3 tw-flex-wrap">
            <h2 class="tw-text-lg tw-font-semibold tw-text-slate-900">{{ optional($precedentes_home)->title ?? '' }}</h2>
            <div class="tw-flex tw-items-center tw-gap-2">
                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-emerald-100 tw-text-emerald-800">
                    Guia Completo
                </span>
                @if($admin)
                    <a href="{{ route('content.edit', 'precedentes-home') }}" class="tw-text-slate-400 hover:tw-text-brand-600 tw-transition-colors" title="Editar conteúdo">
                        <i class="fa fa-pencil"></i>
                    </a>
                @endif
            </div>
        </header>
        <div class="tw-p-6 tw-prose tw-prose-slate tw-max-w-none">
            {!! optional($precedentes_home)->content ?? '' !!}
        </div>
    </section>
    @endif

    <!-- Temas Mais Consultados -->
    @if(isset($popular_themes) && $popular_themes->count() > 0)
    <section class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden hover:tw-shadow-md tw-transition-shadow">
        <header class="tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-px-6 tw-py-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-slate-900">Temas Mais Consultados</h3>
        </header>
        <div class="tw-p-6">
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4">
                @foreach($popular_themes as $theme)
                <a href="{{ url('/tema/' . $theme->slug) }}" class="tw-group tw-block tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 hover:tw-border-brand-300 hover:tw-bg-brand-50 hover:tw-shadow-sm tw-transition-all tw-no-underline">
                    <div class="tw-text-sm tw-font-medium tw-text-slate-700 group-hover:tw-text-brand-800">
                        {{ $theme->label ?? $theme->keyword }}
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Quizzes Jurídicos -->
    @if(isset($featured_quizzes) && $featured_quizzes->count() > 0)
    <section class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden hover:tw-shadow-md tw-transition-shadow">
        <header class="tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between tw-gap-3 tw-flex-wrap">
            <div class="tw-flex tw-items-center tw-gap-2">
                <i class="fa fa-graduation-cap tw-text-brand-600 tw-text-lg"></i>
                <h3 class="tw-text-lg tw-font-semibold tw-text-slate-900">Teste seus Conhecimentos</h3>
            </div>
            <a href="{{ route('quizzes.index') }}" class="tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 tw-transition-colors">
                Ver Todos <i class="fa fa-arrow-right tw-text-xs"></i>
            </a>
        </header>
        <div class="tw-p-6">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
                @foreach($featured_quizzes as $quiz)
                <a href="{{ route('quiz.show', $quiz->slug) }}" class="tw-group tw-block tw-h-full tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-5 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all tw-no-underline tw-border-l-4 tw-border-l-brand-500">
                    <div class="tw-font-semibold tw-text-slate-800 group-hover:tw-text-brand-700 tw-mb-3 tw-line-clamp-2">
                        {{ $quiz->title }}
                    </div>
                    <div class="tw-space-y-2 tw-text-sm tw-text-slate-600">
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <i class="fa fa-question-circle tw-w-4 tw-text-center tw-text-slate-400"></i>
                            <span>{{ $quiz->questions_count }} questões</span>
                        </div>
                        @if($quiz->tribunal)
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <i class="fa fa-building tw-w-4 tw-text-center tw-text-slate-400"></i>
                            <span>{{ $quiz->tribunal }}</span>
                        </div>
                        @endif
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <i class="fa fa-signal tw-w-4 tw-text-center tw-text-slate-400"></i>
                            @if($quiz->difficulty == 'easy')
                                <span class="tw-text-emerald-600 tw-font-medium">Fácil</span>
                            @elseif($quiz->difficulty == 'hard')
                                <span class="tw-text-red-600 tw-font-medium">Difícil</span>
                            @else
                                <span class="tw-text-amber-600 tw-font-medium">Médio</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif


</div>

@endsection

