@extends('front.base')

@section('page-title', 'Resultado - ' . $quiz->title)

@section('content')
<div class="tw-container tw-mx-auto tw-px-4 tw-py-8 tw-max-w-4xl">
    
    <!-- Breadcrumbs -->
    <nav class="tw-flex tw-text-sm tw-text-slate-500 tw-mb-8 hidden md:tw-flex" aria-label="Breadcrumb">
        <ol class="tw-inline-flex tw-items-center tw-space-x-1 md:tw-space-x-3">
            <li class="tw-inline-flex tw-items-center">
                <a href="{{ url('/') }}" class="tw-inline-flex tw-items-center tw-text-slate-500 hover:tw-text-brand-600">
                    <i class="fa fa-home tw-mr-2"></i> Início
                </a>
            </li>
            <li class="tw-inline-flex tw-items-center">
                <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                <a href="{{ route('quizzes.index') }}" class="tw-text-slate-500 hover:tw-text-brand-600">Quizzes</a>
            </li>
            @if($quiz->category)
            <li class="tw-inline-flex tw-items-center">
                <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                <a href="{{ route('quizzes.category', $quiz->category->slug) }}" class="tw-text-slate-500 hover:tw-text-brand-600">{{ $quiz->category->name }}</a>
            </li>
            @endif
            <li class="tw-inline-flex tw-items-center">
                <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                <a href="{{ route('quiz.show', $quiz->slug) }}" class="tw-text-slate-500 hover:tw-text-brand-600">{{ Str::limit($quiz->title, 30) }}</a>
            </li>
            <li class="tw-inline-flex tw-items-center">
                <i class="fa fa-chevron-right tw-text-slate-400 tw-text-xs tw-mx-2"></i>
                <span class="tw-text-slate-700 tw-font-medium">Resultado</span>
            </li>
        </ol>
    </nav>

    <!-- Result Card -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden tw-mb-8">
        @php
            $percentage = $attempt->score_percentage;
            $scoreClass = $percentage >= 80 ? 'tw-text-emerald-500' : ($percentage >= 60 ? 'tw-text-blue-500' : ($percentage >= 40 ? 'tw-text-amber-500' : 'tw-text-rose-500'));
            $bgClass = $percentage >= 80 ? 'tw-bg-emerald-50' : ($percentage >= 60 ? 'tw-bg-blue-50' : ($percentage >= 40 ? 'tw-bg-amber-50' : 'tw-bg-rose-50'));
            $emoji = $percentage >= 80 ? '🎉' : ($percentage >= 60 ? '👍' : ($percentage >= 40 ? '📚' : '💪'));
            $message = $percentage >= 80 
                ? 'Excelente! Você domina este tema!' 
                : ($percentage >= 60 
                    ? 'Bom trabalho! Continue estudando para melhorar ainda mais.' 
                    : ($percentage >= 40 
                        ? 'Você está no caminho certo. Revise o conteúdo para melhorar.' 
                        : 'Não desanime! Revise as teses e tente novamente.'));
        @endphp
        
        <div class="tw-p-8 tw-text-center tw-bg-brand-600 tw-bg-gradient-to-br tw-from-brand-600 tw-to-brand-800 tw-text-white">
            <div class="tw-text-6xl tw-mb-4 tw-filter tw-drop-shadow-md">{{ $emoji }}</div>
            <h1 class="tw-text-2xl tw-font-bold tw-mb-1">{{ $quiz->title }}</h1>
            <p class="tw-text-brand-100 tw-font-medium">Quiz Finalizado!</p>
        </div>
        
        <div class="tw-grid tw-grid-cols-3 tw-divide-x tw-divide-slate-100 tw-border-b tw-border-slate-100 tw-bg-slate-50">
            <div class="tw-p-6 tw-text-center">
                <div class="tw-text-3xl tw-font-bold tw-text-brand-600 tw-mb-1">{{ $attempt->score }}</div>
                <div class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">Acertos</div>
            </div>
            <div class="tw-p-6 tw-text-center">
                <div class="tw-text-4xl tw-font-bold {{ $scoreClass }} tw-mb-1">{{ number_format($percentage, 0) }}%</div>
                <div class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">Aproveitamento</div>
            </div>
            <div class="tw-p-6 tw-text-center">
                <div class="tw-text-3xl tw-font-bold tw-text-slate-700 tw-mb-1">{{ $attempt->total_questions }}</div>
                <div class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider">Questões</div>
            </div>
        </div>
        
        <div class="tw-p-8 md:tw-p-10">
            <div class="tw-text-center tw-mb-8 tw-bg-slate-50 tw-rounded-xl tw-p-6 {{ $bgClass }} tw-bg-opacity-50">
                <h4 class="tw-text-lg tw-font-bold tw-text-slate-800 tw-mb-2">{{ $message }}</h4>
                @if($attempt->formatted_time != '--')
                    <p class="tw-text-slate-600 tw-text-sm">
                        <i class="fa fa-clock-o tw-mr-1"></i> Tempo total: {{ $attempt->formatted_time }}
                    </p>
                @endif
            </div>
            
            <div class="tw-flex tw-flex-wrap tw-justify-center tw-gap-4">
                <a href="{{ route('quiz.restart', $quiz->slug) }}" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-brand-600 tw-text-white tw-font-semibold tw-rounded-lg tw-shadow-md hover:tw-bg-brand-700 hover:tw-shadow-lg tw-transition-all hover:tw--translate-y-0.5">
                    <i class="fa fa-refresh tw-mr-2"></i> Tentar Novamente
                </a>
                <a href="{{ route('quizzes.index') }}" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-white tw-text-brand-600 tw-font-semibold tw-rounded-lg tw-border tw-border-brand-200 hover:tw-bg-brand-50 hover:tw-border-brand-300 tw-transition-all">
                    <i class="fa fa-th-large tw-mr-2"></i> Outros Quizzes
                </a>
                @if($quiz->tema_number && $quiz->tribunal)
                    <a href="/tese/{{ strtolower($quiz->tribunal) }}/{{ $quiz->tema_number }}" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-white tw-text-slate-600 tw-font-semibold tw-rounded-lg tw-border tw-border-slate-200 hover:tw-bg-slate-50 hover:tw-text-slate-800 tw-transition-all">
                        <i class="fa fa-book tw-mr-2"></i> Ver Tese
                    </a>
                @endif
            </div>
        </div>
        
        @if($quiz->show_share)
        <div class="tw-bg-slate-50 tw-p-6 tw-border-t tw-border-slate-100 tw-text-center">
            <p class="tw-text-slate-500 tw-text-sm tw-font-medium tw-mb-4">Compartilhe seu resultado:</p>
            <div class="tw-flex tw-justify-center tw-gap-2 tw-flex-wrap">
                <a href="https://wa.me/?text={{ urlencode('Fiz ' . $percentage . '% no quiz "' . $quiz->title . '" no Teses & Súmulas! ' . url()->current()) }}" 
                   target="_blank" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-[#25D366] tw-text-white tw-rounded-lg tw-text-sm tw-font-bold hover:tw-opacity-90 tw-transition-opacity">
                    <i class="fa fa-whatsapp tw-mr-2"></i> WhatsApp
                </a>
                <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode('Fiz ' . $percentage . '% no quiz "' . $quiz->title . '"!') }}" 
                   target="_blank" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-[#0088cc] tw-text-white tw-rounded-lg tw-text-sm tw-font-bold hover:tw-opacity-90 tw-transition-opacity">
                    <i class="fa fa-telegram tw-mr-2"></i> Telegram
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode('Fiz ' . $percentage . '% no quiz "' . $quiz->title . '" 🎯⚖️ ' . url()->current()) }}" 
                   target="_blank" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-[#1DA1F2] tw-text-white tw-rounded-lg tw-text-sm tw-font-bold hover:tw-opacity-90 tw-transition-opacity">
                    <i class="fa fa-twitter tw-mr-2"></i> Twitter
                </a>
                <button @click="navigator.clipboard.writeText(window.location.href); alert('Link copiado!')"
                   class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-slate-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-bold hover:tw-bg-slate-600 tw-transition-colors">
                    <i class="fa fa-link tw-mr-2"></i> Copiar Link
                </button>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Review Section -->
    <div class="tw-mb-12">
        <h3 class="tw-text-xl tw-font-bold tw-text-slate-800 tw-mb-6 tw-flex tw-items-center tw-gap-2">
            <i class="fa fa-list-ol tw-text-brand-500"></i> Revise suas respostas
        </h3>
        
        <div class="tw-space-y-4">
            @foreach($attempt->answers as $answer)
                @php
                    $question = $answer->question;
                    $correctOption = $question->options->where('is_correct', true)->first();
                @endphp
                <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden hover:tw-shadow-md tw-transition-shadow" 
                     x-data="{ expanded: {{ $answer->is_correct ? 'false' : 'true' }} }">
                    
                    <button @click="expanded = !expanded" 
                            class="tw-w-full tw-flex tw-items-start tw-justify-between tw-text-left tw-p-5 tw-bg-slate-50 hover:tw-bg-white tw-transition-colors group">
                        <div class="tw-flex-1 tw-pr-4">
                            <span class="tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-mb-1 tw-block">Questão {{ $loop->iteration }}</span>
                            <h4 class="tw-text-slate-700 tw-font-medium group-hover:tw-text-brand-700 tw-transition-colors">
                                {{ Str::limit($question->text, 100) }}
                            </h4>
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-3">
                            @if($answer->is_correct)
                                <span class="tw-flex tw-items-center tw-gap-1 tw-text-sm tw-font-bold tw-text-emerald-600 tw-bg-emerald-50 tw-px-2.5 tw-py-1 tw-rounded-lg">
                                    <i class="fa fa-check-circle"></i> Correta
                                </span>
                            @else
                                <span class="tw-flex tw-items-center tw-gap-1 tw-text-sm tw-font-bold tw-text-rose-600 tw-bg-rose-50 tw-px-2.5 tw-py-1 tw-rounded-lg">
                                    <i class="fa fa-times-circle"></i> Incorreta
                                </span>
                            @endif
                            <i class="fa fa-chevron-down tw-text-slate-400 tw-transition-transform" :class="expanded ? 'tw-rotate-180' : ''"></i>
                        </div>
                    </button>
                    
                    <div x-show="expanded" 
                         x-collapse
                         class="tw-border-t tw-border-slate-100">
                        <div class="tw-p-6">
                            <div class="tw-mb-6 tw-text-lg tw-text-slate-800 tw-font-medium">
                                {{ $question->text }}
                            </div>
                            
                            <div class="tw-space-y-3">
                                @foreach($question->options as $option)
                                    @php
                                        $isSelected = $answer->selected_option_id == $option->id;
                                        $isCorrect = $option->is_correct;
                                    @endphp
                                    <div class="tw-p-4 tw-rounded-lg tw-flex tw-items-start tw-gap-3 {{ $isSelected && $isCorrect ? 'tw-bg-emerald-100 tw-border tw-border-emerald-200' : ($isSelected && !$isCorrect ? 'tw-bg-rose-100 tw-border tw-border-rose-200' : ($isCorrect ? 'tw-bg-emerald-50 tw-border tw-border-emerald-200' : 'tw-bg-slate-50 tw-border tw-border-slate-100')) }}">
                                        <div class="tw-w-6 tw-h-6 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-sm tw-font-bold {{ $isSelected && $isCorrect ? 'tw-bg-emerald-500 tw-text-white' : ($isSelected && !$isCorrect ? 'tw-bg-rose-500 tw-text-white' : ($isCorrect ? 'tw-bg-emerald-200 tw-text-emerald-700' : 'tw-bg-slate-200 tw-text-slate-500')) }}">
                                            {{ $option->letter }}
                                        </div>
                                        <div class="tw-flex-1 {{ $isSelected || $isCorrect ? 'tw-font-medium' : '' }}">
                                            {{ $option->text }}
                                        </div>
                                        
                                        @if($isCorrect)
                                            <i class="fa fa-check tw-text-emerald-600"></i>
                                        @endif
                                        @if($isSelected && !$isCorrect)
                                            <i class="fa fa-times tw-text-rose-600"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            
                            @if($question->explanation)
                                <div class="tw-mt-6 tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-5 tw-rounded-r-lg">
                                    <h5 class="tw-text-blue-800 tw-font-bold tw-flex tw-items-center tw-gap-2 tw-mb-2">
                                        <i class="fa fa-lightbulb-o"></i> Explicação
                                    </h5>
                                    <div class="tw-text-slate-700 tw-leading-relaxed">
                                        {{ $question->explanation }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
