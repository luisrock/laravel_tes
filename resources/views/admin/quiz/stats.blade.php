@extends('layouts.admin')

@section('content')
<div class="tw-max-w-6xl tw-mx-auto">
    <!-- Header -->
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-8">
        <div>
            <h2 class="tw-text-2xl tw-font-bold tw-text-slate-800">Estatísticas de Quizzes</h2>
            <p class="tw-text-slate-500">Visão geral do desempenho dos quizzes</p>
        </div>
        <div>
            <a href="{{ route('admin.quizzes.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-slate-300 tw-rounded-md tw-font-semibold tw-text-xs tw-text-slate-700 tw-uppercase tw-tracking-widest tw-shadow-sm hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-ring-offset-2 disabled:tw-opacity-25 tw-transition text-decoration-none">
                <i class="fas fa-arrow-left tw-mr-2"></i> Voltar aos Quizzes
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-6 tw-gap-4 tw-mb-8">
        <!-- Total Quizzes -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-3xl tw-font-bold tw-text-brand-600">{{ $stats['total_quizzes'] }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Quizzes</div>
        </div>

        <!-- Total Questions -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-3xl tw-font-bold tw-text-brand-600">{{ $stats['total_questions'] }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Perguntas</div>
        </div>

        <!-- Total Attempts -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-3xl tw-font-bold tw-text-brand-600">{{ $stats['total_attempts'] }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Tentativas</div>
        </div>

        <!-- Completed -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-3xl tw-font-bold tw-text-emerald-500">{{ $stats['completed_attempts'] }}</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Completos</div>
        </div>

        <!-- Completion Rate -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-3xl tw-font-bold tw-text-amber-500">{{ number_format($stats['completion_rate'], 0) }}%</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Taxa Conclusão</div>
        </div>

        <!-- Average Score -->
        <div class="tw-bg-white tw-rounded-xl tw-p-4 tw-shadow-sm tw-border tw-border-slate-200 tw-text-center">
            <div class="tw-text-3xl tw-font-bold tw-text-blue-500">{{ number_format($stats['average_score'], 0) }}%</div>
            <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mt-1">Média Acertos</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 tw-mb-8">
        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-mb-4 flex tw-items-center">
            <i class="fas fa-chart-line tw-mr-2 tw-text-brand-500"></i> Tentativas nos Últimos 30 Dias
        </h3>
        <div class="tw-relative tw-h-64 tw-w-full">
            <canvas id="attemptsChart"></canvas>
        </div>
    </div>

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-8">
        <!-- Popular Quizzes -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
            <div class="tw-bg-slate-50 tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-flex tw-justify-between tw-items-center">
                <h3 class="tw-font-semibold tw-text-slate-700 tw-text-sm tw-uppercase tw-tracking-wide">
                    <i class="fas fa-trophy tw-mr-2 tw-text-amber-500"></i> Quizzes Mais Populares
                </h3>
            </div>
            <div class="tw-overflow-x-auto">
                <table class="tw-w-full tw-text-sm tw-text-left">
                    <thead class="tw-bg-slate-50 tw-text-slate-500 tw-font-medium">
                        <tr>
                            <th class="tw-px-6 tw-py-3">Quiz</th>
                            <th class="tw-px-6 tw-py-3 tw-text-center">Completados</th>
                            <th class="tw-px-6 tw-py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="tw-divide-y tw-divide-slate-100">
                        @forelse($popularQuizzes as $quiz)
                            <tr class="hover:tw-bg-slate-50 tw-transition">
                                <td class="tw-px-6 tw-py-3">
                                    <a href="{{ route('admin.quizzes.stats.quiz', $quiz) }}" class="tw-text-slate-700 tw-font-medium hover:tw-text-brand-600 text-decoration-none">
                                        {{ Str::limit($quiz->title, 40) }}
                                    </a>
                                </td>
                                <td class="tw-px-6 tw-py-3 tw-text-center">
                                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-800">
                                        {{ $quiz->attempts_count }}
                                    </span>
                                </td>
                                <td class="tw-px-6 tw-py-3 tw-text-right">
                                    <a href="{{ route('admin.quizzes.stats.quiz', $quiz) }}" class="tw-text-slate-400 hover:tw-text-brand-600">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="tw-px-6 tw-py-8 tw-text-center tw-text-slate-500">
                                    Nenhum quiz completado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hardest Questions -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
            <div class="tw-bg-slate-50 tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-flex tw-justify-between tw-items-center">
                <h3 class="tw-font-semibold tw-text-slate-700 tw-text-sm tw-uppercase tw-tracking-wide">
                    <i class="fas fa-exclamation-triangle tw-mr-2 tw-text-red-500"></i> Perguntas Mais Difíceis
                </h3>
            </div>
            <div class="tw-overflow-x-auto">
                <table class="tw-w-full tw-text-sm tw-text-left">
                    <thead class="tw-bg-slate-50 tw-text-slate-500 tw-font-medium">
                        <tr>
                            <th class="tw-px-6 tw-py-3">Pergunta</th>
                            <th class="tw-px-6 tw-py-3 tw-text-center tw-w-32">Taxa Acerto</th>
                        </tr>
                    </thead>
                    <tbody class="tw-divide-y tw-divide-slate-100">
                        @forelse($hardestQuestions as $question)
                            <tr class="hover:tw-bg-slate-50 tw-transition">
                                <td class="tw-px-6 tw-py-3">
                                    <a href="{{ route('admin.questions.edit', $question) }}" class="tw-text-slate-700 hover:tw-text-brand-600 text-decoration-none tw-block">
                                        {{ Str::limit(strip_tags($question->text), 60) }}
                                    </a>
                                </td>
                                <td class="tw-px-6 tw-py-3">
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <span class="tw-text-xs tw-font-medium tw-w-8 tw-text-right">{{ number_format($question->success_rate, 0) }}%</span>
                                        <div class="tw-flex-grow tw-h-1.5 tw-bg-slate-100 tw-rounded-full tw-overflow-hidden">
                                            <div class="tw-h-full tw-rounded-full {{ $question->success_rate < 30 ? 'tw-bg-red-500' : ($question->success_rate < 60 ? 'tw-bg-amber-500' : 'tw-bg-emerald-500') }}" 
                                                 style="width: {{ $question->success_rate }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="tw-px-6 tw-py-8 tw-text-center tw-text-slate-500">
                                    Dados insuficientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Attempts -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-overflow-hidden">
        <div class="tw-bg-slate-50 tw-px-6 tw-py-4 tw-border-b tw-border-slate-100">
            <h3 class="tw-font-semibold tw-text-slate-700 tw-text-sm tw-uppercase tw-tracking-wide">
                <i class="fas fa-clock tw-mr-2 tw-text-slate-400"></i> Tentativas Recentes
            </h3>
        </div>
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm tw-text-left">
                <thead class="tw-bg-slate-50 tw-text-slate-500 tw-font-medium">
                    <tr>
                        <th class="tw-px-6 tw-py-3">Quiz</th>
                        <th class="tw-px-6 tw-py-3">Usuário</th>
                        <th class="tw-px-6 tw-py-3 tw-text-center">Resultado</th>
                        <th class="tw-px-6 tw-py-3 tw-text-center">Status</th>
                        <th class="tw-px-6 tw-py-3 tw-text-right">Data</th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-slate-100">
                    @forelse($recentAttempts as $attempt)
                        <tr class="hover:tw-bg-slate-50 tw-transition">
                            <td class="tw-px-6 tw-py-3">
                                <a href="{{ route('admin.quizzes.stats.quiz', $attempt->quiz) }}" class="tw-text-slate-700 tw-font-medium hover:tw-text-brand-600 text-decoration-none">
                                    {{ Str::limit($attempt->quiz->title, 40) }}
                                </a>
                            </td>
                            <td class="tw-px-6 tw-py-3 tw-text-slate-600">
                                @if($attempt->user)
                                    {{ $attempt->user->name }}
                                @else
                                    <span class="tw-text-slate-400 tw-italic">Anônimo</span>
                                @endif
                            </td>
                            <td class="tw-px-6 tw-py-3 tw-text-center">
                                @if($attempt->status == 'completed')
                                    <div class="tw-inline-flex tw-flex-col">
                                        <span class="tw-font-bold tw-text-slate-700">{{ $attempt->score }}/{{ $attempt->total_questions }}</span>
                                        <span class="tw-text-xs tw-text-slate-500">({{ $attempt->score_percentage }}%)</span>
                                    </div>
                                @else
                                    <span class="tw-text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="tw-px-6 tw-py-3 tw-text-center">
                                @if($attempt->status == 'completed')
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-emerald-100 tw-text-emerald-800">
                                        Completo
                                    </span>
                                @elseif($attempt->status == 'in_progress')
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-amber-100 tw-text-amber-800">
                                        Andamento
                                    </span>
                                @else
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-800">
                                        Abandonado
                                    </span>
                                @endif
                            </td>
                            <td class="tw-px-6 tw-py-3 tw-text-right tw-text-slate-500 tw-text-xs">
                                {{ $attempt->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="tw-px-6 tw-py-8 tw-text-center tw-text-slate-500">
                                Nenhuma tentativa registrada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attemptsChart').getContext('2d');
    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(chartData).map(d => {
                const date = new Date(d);
                // Ajuste para fuso horário local se necessário, ou usar string direta
                const day = String(date.getDate() + 1).padStart(2, '0'); // +1 pois Date(string) as vezes pega dia anterior dependendo do fuso
                const month = String(date.getMonth() + 1).padStart(2, '0');
                return `${day}/${month}`;
            }),
            datasets: [{
                label: 'Tentativas',
                data: Object.values(chartData),
                borderColor: '#4f46e5', // Brand color (Indigo 600 approx)
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1e293b',
                    bodyColor: '#475569',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false
                    },
                    ticks: {
                        stepSize: 1,
                        font: {
                            family: "'Inter', sans-serif"
                        },
                        color: '#64748b'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: "'Inter', sans-serif"
                        },
                        color: '#64748b',
                        maxTicksLimit: 10
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
});
</script>
@endpush
@endsection
