@extends('layouts.admin')

@section('content')
<div class="tw-max-w-6xl tw-mx-auto">
    <!-- Header -->
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-8">
        <div>
            <h2 class="tw-text-2xl tw-font-bold tw-text-slate-800">Estatísticas do Quiz</h2>
            <p class="tw-text-slate-500">{{ $quiz->title }}</p>
        </div>
        <div class="tw-flex tw-gap-2">
            <a href="{{ route('admin.quizzes.stats') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-rounded-md tw-font-semibold tw-text-xs tw-text-slate-700 tw-uppercase tw-tracking-widest tw-shadow-sm hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-ring-offset-2 disabled:tw-opacity-25 tw-transition text-decoration-none">
                <i class="fas fa-arrow-left tw-mr-2"></i> Dashboard
            </a>
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-brand-600 tw-border tw-border-transparent tw-rounded-md tw-font-semibold tw-text-xs tw-text-white tw-uppercase tw-tracking-widest hover:tw-bg-brand-700 focus:tw-bg-brand-700 active:tw-bg-brand-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-ring-offset-2 tw-transition text-decoration-none">
                <i class="fas fa-pencil tw-mr-2"></i> Editar Quiz
            </a>
        </div>
    </div>

    <!-- Quiz Info Card -->
    <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 tw-mb-8 tw-border-l-4" style="border-left-color: {{ $quiz->color ?? '#64748b' }};">
        <div class="tw-flex tw-justify-between tw-items-start">
            <div class="tw-w-3/4">
                <h4 class="tw-text-xl tw-font-bold tw-text-slate-800 tw-mb-2">{{ $quiz->title }}</h4>
                <p class="tw-text-slate-500 tw-mb-4">{{ $quiz->description }}</p>
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    @if($quiz->tribunal)
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                            {{ $quiz->tribunal }}
                        </span>
                    @endif
                    @if($quiz->category)
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">
                            {{ $quiz->category->name }}
                        </span>
                    @endif
                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-800">
                        {{ $quiz->questions->count() }} perguntas
                    </span>
                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium {{ $quiz->status == 'published' ? 'tw-bg-emerald-100 tw-text-emerald-800' : 'tw-bg-amber-100 tw-text-amber-800' }}">
                        {{ $quiz->status_label }}
                    </span>
                </div>
            </div>
            <div class="tw-text-right">
                @if($quiz->status == 'published')
                    <a href="{{ route('quiz.show', $quiz->slug) }}" target="_blank" class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-border tw-border-transparent tw-text-xs tw-font-medium tw-rounded-md tw-text-emerald-700 tw-bg-emerald-100 hover:tw-bg-emerald-200 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-emerald-500 text-decoration-none">
                        <i class="fas fa-external-link-alt tw-mr-1.5"></i> Ver no Site
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-6 tw-gap-4 tw-mb-8">
        <!-- Attempts -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-brand-600">{{ $stats['total_attempts'] }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Tentativas</div>
        </div>

        <!-- Completed -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-emerald-500">{{ $stats['completed_attempts'] }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Completos</div>
        </div>

        <!-- Completion Rate -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-amber-500">{{ $stats['completion_rate'] }}%</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Taxa Conclusão</div>
        </div>

        <!-- Average Score -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-blue-500">{{ number_format($stats['average_percentage'], 0) }}%</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Média Acertos</div>
        </div>

        <!-- Average Time -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-slate-600">{{ gmdate('i:s', $stats['average_time']) }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Tempo Médio</div>
        </div>

        <!-- Views -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-2xl tw-font-bold tw-text-slate-600">{{ $quiz->views_count }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Visualizações</div>
        </div>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-8">
        <!-- Question Performance -->
        <div class="lg:tw-col-span-8">
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden tw-mb-8">
                <div class="tw-bg-slate-50 tw-px-6 tw-py-4 tw-border-b tw-border-slate-100">
                    <h3 class="tw-font-semibold tw-text-slate-700 tw-text-sm tw-uppercase tw-tracking-wide">
                        <i class="fas fa-chart-bar tw-mr-2 tw-text-slate-400"></i> Desempenho por Pergunta
                    </h3>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    @forelse($questionStats as $index => $qs)
                        <div class="tw-bg-white tw-rounded-lg tw-border tw-border-slate-200 tw-overflow-hidden">
                            <div class="tw-bg-slate-50 tw-px-4 tw-py-3 tw-flex tw-justify-between tw-items-center">
                                <div>
                                    <span class="tw-font-bold tw-text-slate-700">Pergunta {{ $index + 1 }}</span>
                                    <span class="tw-ml-2 tw-text-xs tw-text-slate-500">{{ $qs['total_answers'] }} respostas</span>
                                </div>
                                <div class="tw-text-sm">
                                    @php
                                        $rate = $qs['success_rate'];
                                        $color = $rate >= 70 ? 'tw-text-emerald-600' : ($rate >= 40 ? 'tw-text-amber-600' : 'tw-text-red-600');
                                        $barColor = $rate >= 70 ? 'tw-bg-emerald-500' : ($rate >= 40 ? 'tw-bg-amber-500' : 'tw-bg-red-500');
                                    @endphp
                                    <span class="tw-font-bold {{ $color }}">{{ $rate }}% acertos</span>
                                </div>
                            </div>
                            <div class="tw-p-4">
                                <p class="tw-text-slate-700 tw-mb-3 tw-text-sm">{{ Str::limit(strip_tags($qs['question']->text), 150) }}</p>
                                
                                <div class="tw-h-2 tw-w-full tw-bg-slate-100 tw-rounded-full tw-overflow-hidden tw-mb-4">
                                    <div class="tw-h-full {{ $barColor }}" style="width: {{ $rate }}%"></div>
                                </div>
                                
                                <div class="tw-flex tw-flex-wrap tw-gap-2">
                                    @foreach($qs['question']->options as $option)
                                        @php
                                            $count = $qs['option_distribution'][$option->letter] ?? 0;
                                            $isCorrect = $option->is_correct;
                                            $optClass = $isCorrect ? 'tw-bg-emerald-50 tw-text-emerald-700 tw-border-emerald-200' : 'tw-bg-slate-50 tw-text-slate-600 tw-border-slate-200';
                                        @endphp
                                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-border {{ $optClass }}">
                                            <span class="tw-font-bold tw-mr-1">{{ $option->letter }}:</span> {{ $count }}
                                            @if($isCorrect) <i class="fas fa-check tw-ml-1 tw-text-emerald-500"></i> @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="tw-text-center tw-py-8 tw-text-slate-500">
                            <i class="fas fa-info-circle tw-mb-2 tw-block tw-text-2xl tw-text-slate-300"></i>
                            Nenhuma resposta registrada ainda.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Attempts -->
        <div class="lg:tw-col-span-4">
            <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
                <div class="tw-bg-slate-50 tw-px-6 tw-py-4 tw-border-b tw-border-slate-100">
                    <h3 class="tw-font-semibold tw-text-slate-700 tw-text-sm tw-uppercase tw-tracking-wide">
                        <i class="fas fa-clock tw-mr-2 tw-text-slate-400"></i> Tentativas Recentes
                    </h3>
                </div>
                <div class="tw-divide-y tw-divide-slate-100">
                    @forelse($recentAttempts as $attempt)
                        <div class="tw-p-4 hover:tw-bg-slate-50 tw-transition">
                            <div class="tw-flex tw-justify-between tw-items-start tw-mb-1">
                                <div class="tw-font-medium tw-text-slate-900">
                                    @if($attempt->user)
                                        {{ $attempt->user->name }}
                                    @else
                                        <span class="tw-text-slate-400 tw-italic">Anônimo</span>
                                    @endif
                                </div>
                                <div class="tw-text-right">
                                    @if($attempt->status == 'completed')
                                        <div class="tw-font-bold tw-text-slate-800">{{ $attempt->score }}/{{ $attempt->total_questions }}</div>
                                        @php
                                            $scoreRate = $attempt->score_percentage;
                                            $scoreColor = $scoreRate >= 70 ? 'tw-text-emerald-600' : ($scoreRate >= 40 ? 'tw-text-amber-600' : 'tw-text-red-600');
                                        @endphp
                                        <div class="tw-text-xs {{ $scoreColor }} tw-font-medium">{{ $scoreRate }}%</div>
                                    @else
                                        <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded text-xs tw-font-medium {{ $attempt->status == 'in_progress' ? 'tw-bg-amber-100 tw-text-amber-800' : 'tw-bg-slate-100 tw-text-slate-800' }}">
                                            {{ $attempt->status == 'in_progress' ? 'Andamento' : 'Abandonado' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="tw-text-xs tw-text-slate-500">
                                {{ $attempt->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="tw-p-8 tw-text-center tw-text-slate-500">
                            Nenhuma tentativa ainda.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
