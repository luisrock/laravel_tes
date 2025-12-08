@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
        height: 100%;
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #5c80d1;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-card.success .stat-number { color: #10b981; }
    .stat-card.warning .stat-number { color: #f59e0b; }
    .stat-card.danger .stat-number { color: #ef4444; }
    
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    .chart-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .table-card .card-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .table-card .table {
        margin-bottom: 0;
    }
    .table-card .table th {
        border-top: none;
        background: #f8f9fa;
    }
    
    .progress-mini {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
    }
    .progress-mini-bar {
        height: 100%;
        border-radius: 3px;
    }
    .progress-mini-bar.success { background: #10b981; }
    .progress-mini-bar.warning { background: #f59e0b; }
    .progress-mini-bar.danger { background: #ef4444; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Estatísticas de Quizzes</h2>
                    <p class="text-muted mb-0">Visão geral do desempenho dos quizzes</p>
                </div>
                <div>
                    <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i> Voltar aos Quizzes
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['total_quizzes'] }}</div>
                        <div class="stat-label">Quizzes</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['total_questions'] }}</div>
                        <div class="stat-label">Perguntas</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['total_attempts'] }}</div>
                        <div class="stat-label">Tentativas</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card success">
                        <div class="stat-number">{{ $stats['completed_attempts'] }}</div>
                        <div class="stat-label">Completos</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card warning">
                        <div class="stat-number">{{ number_format($stats['completion_rate'], 0) }}%</div>
                        <div class="stat-label">Taxa Conclusão</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format($stats['average_score'], 0) }}%</div>
                        <div class="stat-label">Média Acertos</div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <h5 class="chart-title"><i class="fa fa-line-chart"></i> Tentativas nos Últimos 30 Dias</h5>
                <canvas id="attemptsChart" height="80"></canvas>
            </div>

            <div class="row">
                <!-- Popular Quizzes -->
                <div class="col-md-6 mb-4">
                    <div class="table-card">
                        <div class="card-header">
                            <i class="fa fa-trophy"></i> Quizzes Mais Populares
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th class="text-center">Completados</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($popularQuizzes as $quiz)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.quizzes.stats.quiz', $quiz) }}">
                                                {{ Str::limit($quiz->title, 40) }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $quiz->attempts_count }}</strong>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.quizzes.stats.quiz', $quiz) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-bar-chart"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            Nenhum quiz completado ainda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Hardest Questions -->
                <div class="col-md-6 mb-4">
                    <div class="table-card">
                        <div class="card-header">
                            <i class="fa fa-exclamation-triangle text-danger"></i> Perguntas Mais Difíceis
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Pergunta</th>
                                    <th class="text-center">Taxa Acerto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hardestQuestions as $question)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.questions.edit', $question) }}">
                                                {{ Str::limit($question->text, 50) }}
                                            </a>
                                        </td>
                                        <td style="width: 120px;">
                                            <div class="d-flex align-items-center">
                                                <span class="mr-2" style="width: 40px;">{{ number_format($question->success_rate, 0) }}%</span>
                                                <div class="progress-mini flex-grow-1">
                                                    <div class="progress-mini-bar {{ $question->success_rate < 30 ? 'danger' : ($question->success_rate < 60 ? 'warning' : 'success') }}" 
                                                         style="width: {{ $question->success_rate }}%;"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
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
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-clock-o"></i> Tentativas Recentes</span>
                </div>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Usuário</th>
                            <th class="text-center">Resultado</th>
                            <th class="text-center">Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttempts as $attempt)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.quizzes.stats.quiz', $attempt->quiz) }}">
                                        {{ Str::limit($attempt->quiz->title, 40) }}
                                    </a>
                                </td>
                                <td>
                                    @if($attempt->user)
                                        {{ $attempt->user->name }}
                                    @else
                                        <span class="text-muted">Anônimo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($attempt->status == 'completed')
                                        <strong>{{ $attempt->score }}/{{ $attempt->total_questions }}</strong>
                                        <small class="text-muted">({{ $attempt->score_percentage }}%)</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($attempt->status == 'completed')
                                        <span class="badge badge-success">Completo</span>
                                    @elseif($attempt->status == 'in_progress')
                                        <span class="badge badge-warning">Em andamento</span>
                                    @else
                                        <span class="badge badge-secondary">Abandonado</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $attempt->created_at->diffForHumans() }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhuma tentativa registrada ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
<script>
const ctx = document.getElementById('attemptsChart').getContext('2d');
const chartData = @json($chartData);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: Object.keys(chartData).map(d => {
            const date = new Date(d);
            return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        }),
        datasets: [{
            label: 'Tentativas',
            data: Object.values(chartData),
            borderColor: '#5c80d1',
            backgroundColor: 'rgba(92, 128, 209, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endsection
