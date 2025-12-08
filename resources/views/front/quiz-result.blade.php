@extends('front.base')

@section('page-title', 'Resultado - ' . $quiz->title)

@section('styles')
<style>
    :root {
        --quiz-color: #5c80d1;
        --primary-color: #5c80d1;
        --text-color: #575757;
        --text-muted: #6c757d;
        --bg-light: #f5f5f5;
        --border-color: #ebebeb;
    }
    
    /* ========================================
       QUIZ RESULT PAGE - SITE CONSISTENT
       ======================================== */
    
    /* Breadcrumbs */
    .result-breadcrumb {
        background: white;
        border-bottom: 1px solid var(--border-color);
        padding: 0.75rem 0;
    }
    
    .result-breadcrumb-content {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    .breadcrumb-list {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 0.875rem;
        flex-wrap: wrap;
    }
    
    .breadcrumb-list a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .breadcrumb-list a:hover {
        text-decoration: underline;
    }
    
    .breadcrumb-list .separator {
        color: #adb5bd;
        font-size: 0.75rem;
    }
    
    .breadcrumb-list .current {
        color: var(--text-muted);
    }
    
    /* Main Content */
    .result-page-main {
        max-width: 900px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
    }
    
    /* Result Card */
    .result-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .result-header {
        background: var(--quiz-color);
        color: white;
        padding: 2rem 1.5rem;
        text-align: center;
    }
    
    .result-emoji {
        font-size: 3.5rem;
        margin-bottom: 1rem;
    }
    
    .result-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .result-subtitle {
        opacity: 0.95;
        font-size: 0.9375rem;
    }
    
    /* Score Container */
    .score-container {
        display: flex;
        justify-content: center;
        gap: 2rem;
        padding: 2rem 1.5rem;
        background: var(--bg-light);
    }
    
    .score-item {
        text-align: center;
    }
    
    .score-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--quiz-color);
        line-height: 1;
    }
    
    .score-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.375rem;
        font-weight: 600;
    }
    
    .score-percentage {
        font-size: 2.5rem;
        font-weight: 700;
    }
    
    .score-percentage.excellent { color: #10b981; }
    .score-percentage.good { color: #3b82f6; }
    .score-percentage.average { color: #f59e0b; }
    .score-percentage.poor { color: #ef4444; }
    
    /* Result Body */
    .result-body {
        padding: 2.5rem;
    }
    
    .result-message {
        text-align: center;
        padding: 1.5rem 2rem;
        background: #f8f9fa;
        border-radius: 14px;
        margin-bottom: 2rem;
    }
    
    .result-message h4 {
        margin-bottom: 0.625rem;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a1a2e;
    }
    
    .result-message .time-info {
        font-size: 0.9375rem;
        color: #6c757d;
        margin: 0;
    }
    
    .result-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .result-actions .btn {
        padding: 0.875rem 1.75rem;
        font-weight: 600;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s ease;
    }
    
    .result-actions .btn:hover {
        transform: translateY(-2px);
    }
    
    .result-actions .btn-primary {
        background: var(--quiz-color);
        border-color: var(--quiz-color);
    }
    
    .result-actions .btn-primary:hover {
        filter: brightness(1.1);
    }
    
    /* Share Section */
    .share-section {
        padding: 2rem 2.5rem;
        border-top: 1px solid #e9ecef;
        text-align: center;
    }
    
    .share-title {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 1.25rem;
        font-weight: 500;
    }
    
    .share-buttons {
        display: flex;
        justify-content: center;
        gap: 0.625rem;
        flex-wrap: wrap;
    }
    
    .share-btn {
        padding: 0.625rem 1.25rem;
        border: none;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s ease;
        text-decoration: none;
        color: white;
    }
    
    .share-btn.whatsapp { background: #25D366; }
    .share-btn.telegram { background: #0088cc; }
    .share-btn.twitter { background: #1DA1F2; }
    .share-btn.copy { background: #6c757d; }
    
    .share-btn:hover {
        transform: translateY(-3px);
        filter: brightness(1.1);
        color: white;
        text-decoration: none;
    }
    
    /* Review Section */
    .review-section {
        margin-top: 2rem;
    }
    
    .review-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        color: #1a1a2e;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .review-title i {
        color: var(--quiz-color);
    }
    
    .review-item {
        background: white;
        border-radius: 14px;
        border: 2px solid #e9ecef;
        margin-bottom: 1rem;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    
    .review-item:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    .review-item.correct {
        border-left: 5px solid #10b981;
    }
    
    .review-item.incorrect {
        border-left: 5px solid #ef4444;
    }
    
    .review-header {
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        cursor: pointer;
        background: #f8f9fa;
        transition: background 0.2s;
    }
    
    .review-header:hover {
        background: #f0f0f0;
    }
    
    .review-question-preview {
        flex: 1;
    }
    
    .review-question-preview strong {
        display: block;
        margin-bottom: 0.375rem;
        color: #1a1a2e;
    }
    
    .review-question-preview .text-muted {
        font-size: 0.875rem;
    }
    
    .review-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
        margin-left: 1rem;
    }
    
    .review-status.correct { color: #10b981; }
    .review-status.incorrect { color: #ef4444; }
    
    .review-body {
        padding: 1.5rem;
        display: none;
        border-top: 1px solid #e9ecef;
    }
    
    .review-item.expanded .review-body {
        display: block;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .review-question {
        font-weight: 500;
        margin-bottom: 1.25rem;
        line-height: 1.7;
        color: #1a1a2e;
    }
    
    .review-option {
        padding: 0.75rem 1rem;
        border-radius: 10px;
        margin-bottom: 0.625rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #f8f9fa;
        transition: all 0.2s;
    }
    
    .review-option.selected {
        background: #fee2e2;
    }
    
    .review-option.correct {
        background: #d1fae5;
    }
    
    .review-option.selected.correct {
        background: #d1fae5;
    }
    
    .review-option strong {
        color: #495057;
    }
    
    .review-explanation {
        margin-top: 1.5rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        font-size: 0.9375rem;
        line-height: 1.7;
    }
    
    .review-explanation strong {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.625rem;
        color: var(--quiz-color);
    }
    
    /* Mobile Adjustments */
    @media (max-width: 767px) {
        .result-header {
            padding: 2rem 1.5rem;
        }
        
        .result-emoji {
            font-size: 4rem;
        }
        
        .score-container {
            gap: 1.5rem;
            padding: 2rem 1rem;
        }
        
        .score-number {
            font-size: 2rem;
        }
        
        .score-percentage {
            font-size: 2.5rem;
        }
        
        .result-body {
            padding: 1.5rem;
        }
        
        .result-page-nav {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<!-- Breadcrumbs -->
<nav class="result-breadcrumb">
    <div class="result-breadcrumb-content">
        <ol class="breadcrumb-list">
            <li><a href="{{ url('/') }}"><i class="fa fa-home"></i> Início</a></li>
            <li class="separator"><i class="fa fa-chevron-right"></i></li>
            <li><a href="{{ route('quizzes.index') }}">Quizzes</a></li>
            @if($quiz->category)
                <li class="separator"><i class="fa fa-chevron-right"></i></li>
                <li><a href="{{ route('quizzes.category', $quiz->category->slug) }}">{{ $quiz->category->name }}</a></li>
            @endif
            <li class="separator"><i class="fa fa-chevron-right"></i></li>
            <li><a href="{{ route('quiz.show', $quiz->slug) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
            <li class="separator"><i class="fa fa-chevron-right"></i></li>
            <li class="current">Resultado</li>
        </ol>
    </div>
</nav>

<!-- Main Content -->
<div class="result-page-main">
    <!-- Result Card -->
    <div class="result-card">
        @php
            $percentage = $attempt->score_percentage;
            $scoreClass = $percentage >= 80 ? 'excellent' : ($percentage >= 60 ? 'good' : ($percentage >= 40 ? 'average' : 'poor'));
            $emoji = $percentage >= 80 ? '🎉' : ($percentage >= 60 ? '👍' : ($percentage >= 40 ? '📚' : '💪'));
            $message = $percentage >= 80 
                ? 'Excelente! Você domina este tema!' 
                : ($percentage >= 60 
                    ? 'Bom trabalho! Continue estudando para melhorar ainda mais.' 
                    : ($percentage >= 40 
                        ? 'Você está no caminho certo. Revise o conteúdo para melhorar.' 
                        : 'Não desanime! Revise as teses e tente novamente.'));
        @endphp
        
        <div class="result-header">
            <div class="result-emoji">{{ $emoji }}</div>
            <div class="result-title">{{ $quiz->title }}</div>
            <div class="result-subtitle">Quiz Finalizado!</div>
        </div>
        
        <div class="score-container">
            <div class="score-item">
                <div class="score-number">{{ $attempt->score }}</div>
                <div class="score-label">Acertos</div>
            </div>
            <div class="score-item">
                <div class="score-percentage {{ $scoreClass }}">{{ number_format($percentage, 0) }}%</div>
                <div class="score-label">Aproveitamento</div>
            </div>
            <div class="score-item">
                <div class="score-number">{{ $attempt->total_questions }}</div>
                <div class="score-label">Questões</div>
            </div>
        </div>
        
        <div class="result-body">
            <div class="result-message">
                <h4>{{ $message }}</h4>
                @if($attempt->formatted_time != '--')
                    <p class="time-info">
                        <i class="fa fa-clock-o"></i> Tempo total: {{ $attempt->formatted_time }}
                    </p>
                @endif
            </div>
            
            <div class="result-actions">
                <a href="{{ route('quiz.restart', $quiz->slug) }}" class="btn btn-primary">
                    <i class="fa fa-refresh"></i> Tentar Novamente
                </a>
                <a href="{{ route('quizzes.index') }}" class="btn btn-outline-primary">
                    <i class="fa fa-th-large"></i> Outros Quizzes
                </a>
                @if($quiz->tema_number && $quiz->tribunal)
                    <a href="/tese/{{ strtolower($quiz->tribunal) }}/{{ $quiz->tema_number }}" class="btn btn-outline-secondary">
                        <i class="fa fa-book"></i> Ver Tese Relacionada
                    </a>
                @endif
            </div>
        </div>
        
        @if($quiz->show_share)
        <div class="share-section">
            <div class="share-title">Compartilhe seu resultado:</div>
            <div class="share-buttons">
                <a href="https://wa.me/?text={{ urlencode('Fiz ' . $percentage . '% no quiz "' . $quiz->title . '" no Teses & Súmulas! ' . url()->current()) }}" 
                   target="_blank" class="share-btn whatsapp">
                    <i class="fa fa-whatsapp"></i> WhatsApp
                </a>
                <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode('Fiz ' . $percentage . '% no quiz "' . $quiz->title . '"!') }}" 
                   target="_blank" class="share-btn telegram">
                    <i class="fa fa-telegram"></i> Telegram
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode('Fiz ' . $percentage . '% no quiz "' . $quiz->title . '" 🎯⚖️ ' . url()->current()) }}" 
                   target="_blank" class="share-btn twitter">
                    <i class="fa fa-twitter"></i> Twitter
                </a>
                <button class="share-btn copy" onclick="copyLink()">
                    <i class="fa fa-link"></i> Copiar Link
                </button>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Review Section -->
    <div class="review-section">
        <h3 class="review-title">
            <i class="fa fa-list-ol"></i> Revise suas respostas
        </h3>
        
        @foreach($attempt->answers as $answer)
            @php
                $question = $answer->question;
                $correctOption = $question->options->where('is_correct', true)->first();
            @endphp
            <div class="review-item {{ $answer->is_correct ? 'correct' : 'incorrect' }}">
                <div class="review-header" onclick="toggleReview(this)">
                    <div class="review-question-preview">
                        <strong>Questão {{ $loop->iteration }}</strong>
                        <div class="text-muted">
                            {{ Str::limit($question->text, 80) }}
                        </div>
                    </div>
                    <div class="review-status {{ $answer->is_correct ? 'correct' : 'incorrect' }}">
                        @if($answer->is_correct)
                            <i class="fa fa-check-circle"></i> Correta
                        @else
                            <i class="fa fa-times-circle"></i> Incorreta
                        @endif
                    </div>
                </div>
                <div class="review-body">
                    <div class="review-question">{{ $question->text }}</div>
                    
                    @foreach($question->options as $option)
                        @php
                            $isSelected = $answer->selected_option_id == $option->id;
                            $isCorrect = $option->is_correct;
                            $classes = [];
                            if ($isSelected) $classes[] = 'selected';
                            if ($isCorrect) $classes[] = 'correct';
                        @endphp
                        <div class="review-option {{ implode(' ', $classes) }}">
                            <strong>{{ $option->letter }})</strong>
                            <span>{{ $option->text }}</span>
                            @if($isCorrect)
                                <i class="fa fa-check text-success ml-auto"></i>
                            @endif
                            @if($isSelected && !$isCorrect)
                                <i class="fa fa-times text-danger ml-auto"></i>
                            @endif
                        </div>
                    @endforeach
                    
                    @if($question->explanation)
                        <div class="review-explanation">
                            <strong><i class="fa fa-lightbulb-o"></i> Explicação</strong>
                            {{ $question->explanation }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleReview(element) {
    element.closest('.review-item').classList.toggle('expanded');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        const btn = document.querySelector('.share-btn.copy');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check"></i> Copiado!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    });
}

// Expand all incorrect answers by default
document.querySelectorAll('.review-item.incorrect').forEach(item => {
    item.classList.add('expanded');
});
</script>
@endsection
