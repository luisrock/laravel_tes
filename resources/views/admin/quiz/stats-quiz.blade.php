@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
        height: 100%;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #5c80d1;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.8125rem;
        text-transform: uppercase;
    }
    .stat-card.success .stat-number { color: #10b981; }
    .stat-card.warning .stat-number { color: #f59e0b; }
    
    .question-stat {
        background: white;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .question-stat-header {
        padding: 1rem;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .question-stat-body {
        padding: 1rem;
    }
    .success-bar {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
    }
    .success-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    .success-bar-fill.high { background: #10b981; }
    .success-bar-fill.medium { background: #f59e0b; }
    .success-bar-fill.low { background: #ef4444; }
    
    .option-dist {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .option-dist-item {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8125rem;
        background: #e9ecef;
    }
    .option-dist-item.correct {
        background: #d1fae5;
        color: #065f46;
    }
    
    .quiz-header-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        border-left: 4px solid {{ $quiz->color }};
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Estatísticas do Quiz</h2>
                    <p class="text-muted mb-0">{{ $quiz->title }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.quizzes.stats') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fa fa-arrow-left"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-outline-primary">
                        <i class="fa fa-pencil"></i> Editar Quiz
                    </a>
                </div>
            </div>

            <!-- Quiz Info -->
            <div class="quiz-header-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4>{{ $quiz->title }}</h4>
                        <p class="text-muted mb-2">{{ $quiz->description }}</p>
                        <div>
                            @if($quiz->tribunal)
                                <span class="badge badge-primary">{{ $quiz->tribunal }}</span>
                            @endif
                            @if($quiz->category)
                                <span class="badge badge-info">{{ $quiz->category->name }}</span>
                            @endif
                            <span class="badge badge-secondary">{{ $quiz->questions->count() }} perguntas</span>
                            <span class="badge badge-{{ $quiz->status == 'published' ? 'success' : 'warning' }}">
                                {{ $quiz->status_label }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        @if($quiz->status == 'published')
                            <a href="{{ route('quiz.show', $quiz->slug) }}" class="btn btn-success" target="_blank">
                                <i class="fa fa-external-link"></i> Ver no Site
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
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
                        <div class="stat-number">{{ $stats['completion_rate'] }}%</div>
                        <div class="stat-label">Taxa Conclusão</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format($stats['average_percentage'], 0) }}%</div>
                        <div class="stat-label">Média Acertos</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ gmdate('i:s', $stats['average_time']) }}</div>
                        <div class="stat-label">Tempo Médio</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ $quiz->views_count }}</div>
                        <div class="stat-label">Visualizações</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Question Performance -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fa fa-bar-chart"></i> Desempenho por Pergunta
                        </div>
                        <div class="card-body">
                            @forelse($questionStats as $index => $qs)
                                <div class="question-stat">
                                    <div class="question-stat-header">
                                        <div>
                                            <strong>Pergunta {{ $index + 1 }}</strong>
                                            <span class="text-muted ml-2">{{ $qs['total_answers'] }} respostas</span>
                                        </div>
                                        <div>
                                            @php
                                                $rate = $qs['success_rate'];
                                                $class = $rate >= 70 ? 'high' : ($rate >= 40 ? 'medium' : 'low');
                                            @endphp
                                            <strong class="text-{{ $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger') }}">
                                                {{ $rate }}% acertos
                                            </strong>
                                        </div>
                                    </div>
                                    <div class="question-stat-body">
                                        <p class="mb-2">{{ Str::limit($qs['question']->text, 150) }}</p>
                                        
                                        <div class="success-bar mb-3">
                                            <div class="success-bar-fill {{ $class }}" style="width: {{ $rate }}%;"></div>
                                        </div>
                                        
                                        <div class="option-dist">
                                            @foreach($qs['question']->options as $option)
                                                @php
                                                    $count = $qs['option_distribution'][$option->letter] ?? 0;
                                                @endphp
                                                <span class="option-dist-item {{ $option->is_correct ? 'correct' : '' }}">
                                                    {{ $option->letter }}: {{ $count }}
                                                    @if($option->is_correct) ✓ @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fa fa-info-circle"></i> Nenhuma resposta registrada ainda.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Recent Attempts -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-clock-o"></i> Tentativas Recentes
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    @forelse($recentAttempts as $attempt)
                                        <tr>
                                            <td>
                                                <div>
                                                    @if($attempt->user)
                                                        {{ $attempt->user->name }}
                                                    @else
                                                        <span class="text-muted">Anônimo</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $attempt->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td class="text-right">
                                                @if($attempt->status == 'completed')
                                                    <strong>{{ $attempt->score }}/{{ $attempt->total_questions }}</strong>
                                                    <br>
                                                    <small class="text-{{ $attempt->score_percentage >= 70 ? 'success' : ($attempt->score_percentage >= 40 ? 'warning' : 'danger') }}">
                                                        {{ $attempt->score_percentage }}%
                                                    </small>
                                                @else
                                                    <span class="badge badge-{{ $attempt->status == 'in_progress' ? 'warning' : 'secondary' }}">
                                                        {{ $attempt->status == 'in_progress' ? 'Em andamento' : 'Abandonado' }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">
                                                Nenhuma tentativa ainda.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
