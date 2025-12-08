@extends('front.base')

@section('page-title', $quiz->title)

@section('styles')
<style>
    :root {
        --quiz-color: #5c80d1;
        --quiz-color-light: #5c80d115;
        --quiz-color-medium: #5c80d130;
        --primary-color: #5c80d1;
        --text-color: #575757;
        --text-muted: #6c757d;
        --bg-light: #f5f5f5;
        --border-color: #ebebeb;
    }
    
    /* ========================================
       QUIZ PAGE - SITE CONSISTENT
       ======================================== */
    
    /* Breadcrumbs */
    .quiz-breadcrumb {
        background: white;
        border-bottom: 1px solid var(--border-color);
        padding: 0.75rem 0;
    }
    
    .quiz-breadcrumb-content {
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
    
    /* Main Layout */
    .quiz-page-main {
        max-width: 1100px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
    }
    
    .quiz-layout {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 1.5rem;
        align-items: start;
    }
    
    @media (max-width: 991px) {
        .quiz-layout {
            grid-template-columns: 1fr;
        }
    }
    
    /* Quiz Container */
    .quiz-container {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .quiz-header {
        background: var(--quiz-color);
        color: white;
        padding: 1.5rem;
    }
    
    .quiz-category {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.625rem;
        border-radius: 4px;
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    
    .quiz-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0 0 0.5rem;
        line-height: 1.3;
    }
    
    .quiz-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: 0.8125rem;
        opacity: 0.95;
    }
    
    .quiz-meta span {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }
    
    /* Progress Bar */
    .progress-section {
        padding: 1rem 1.5rem;
        background: var(--bg-light);
        border-bottom: 1px solid var(--border-color);
    }
    
    .progress-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.8125rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }
    
    .progress-bar-wrapper {
        background: #e9ecef;
        border-radius: 4px;
        height: 8px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: var(--quiz-color);
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    /* Question Container */
    .question-container {
        padding: 2rem;
    }
    
    .question-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: var(--quiz-color);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 1.125rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .question-text {
        font-size: 1.125rem;
        font-weight: 500;
        line-height: 1.8;
        margin-bottom: 1.75rem;
        color: #1a1a2e;
    }
    
    /* Options */
    .options-list {
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
    }
    
    .option {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.125rem 1.25rem;
        border: 2px solid #e9ecef;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
    }
    
    .option:hover:not(.disabled) {
        border-color: var(--quiz-color);
        background: var(--quiz-color-light);
        transform: translateX(4px);
    }
    
    .option.selected {
        border-color: var(--quiz-color);
        background: var(--quiz-color-light);
        box-shadow: 0 4px 12px var(--quiz-color-medium);
    }
    
    .option.correct {
        border-color: #10b981;
        background: #ecfdf5;
    }
    
    .option.incorrect {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .option.disabled {
        cursor: default;
    }
    
    .option-letter {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9375rem;
        flex-shrink: 0;
        transition: all 0.25s ease;
    }
    
    .option:hover:not(.disabled) .option-letter,
    .option.selected .option-letter {
        background: var(--quiz-color);
        color: white;
    }
    
    .option.correct .option-letter {
        background: #10b981;
        color: white;
    }
    
    .option.incorrect .option-letter {
        background: #ef4444;
        color: white;
    }
    
    .option-text {
        flex: 1;
        font-size: 0.9375rem;
        line-height: 1.6;
        padding-top: 0.375rem;
    }
    
    .option-icon {
        font-size: 1.375rem;
        display: none;
        padding-top: 0.25rem;
    }
    
    .option.correct .option-icon.correct-icon,
    .option.incorrect .option-icon.incorrect-icon {
        display: block;
    }
    
    .option.correct .option-icon { color: #10b981; }
    .option.incorrect .option-icon { color: #ef4444; }
    
    /* Confirm Button */
    .confirm-btn {
        display: none;
        width: 100%;
        padding: 1.125rem;
        margin-top: 2rem;
        border: none;
        border-radius: 14px;
        background: var(--quiz-color);
        color: white;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px var(--quiz-color-medium);
    }
    
    .confirm-btn.show {
        display: block;
    }
    
    .confirm-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px var(--quiz-color-medium);
    }
    
    /* Feedback */
    .feedback {
        display: none;
        margin-top: 2rem;
        padding: 1.5rem;
        border-radius: 14px;
        border-left: 5px solid #10b981;
        background: #ecfdf5;
    }
    
    .feedback.incorrect {
        border-left-color: #ef4444;
        background: #fef2f2;
    }
    
    .feedback.show {
        display: block;
        animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes slideIn {
        from { 
            opacity: 0; 
            transform: translateY(-15px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }
    
    .feedback-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.875rem;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .feedback-header i {
        font-size: 1.375rem;
    }
    
    .feedback.incorrect .feedback-header i { color: #ef4444; }
    .feedback:not(.incorrect) .feedback-header i { color: #10b981; }
    
    .feedback-text {
        font-size: 0.9375rem;
        line-height: 1.7;
        color: #374151;
    }
    
    /* Navigation */
    .quiz-navigation {
        display: none;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e9ecef;
    }
    
    .quiz-navigation.show {
        display: flex;
    }
    
    .nav-btn {
        flex: 1;
        padding: 1rem 1.5rem;
        border: 2px solid var(--quiz-color);
        border-radius: 12px;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        text-decoration: none;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .nav-btn.outline {
        background: white;
        color: var(--quiz-color);
    }
    
    .nav-btn.outline:hover {
        background: var(--quiz-color-light);
    }
    
    .nav-btn.primary {
        background: var(--quiz-color);
        color: white;
    }
    
    .nav-btn.primary:hover {
        filter: brightness(1.1);
        color: white;
        text-decoration: none;
    }
    
    /* Sidebar */
    .quiz-sidebar {
        position: sticky;
        top: 1.5rem;
    }
    
    .sidebar-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    
    .sidebar-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #1a1a2e;
    }
    
    .sidebar-card-header i {
        color: var(--quiz-color);
    }
    
    .sidebar-card-body {
        padding: 1.25rem;
    }
    
    .progress-stats {
        display: flex;
        justify-content: space-around;
        text-align: center;
    }
    
    .stat-item {
        padding: 0.5rem;
    }
    
    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--quiz-color);
        line-height: 1;
    }
    
    .stat-number.correct { color: #10b981; }
    .stat-number.wrong { color: #ef4444; }
    
    .stat-label {
        font-size: 0.6875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.375rem;
    }
    
    .ad-space {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .ad-space small {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Mobile Adjustments */
    @media (max-width: 767px) {
        .quiz-header, .question-container {
            padding: 1.5rem;
        }
        
        .quiz-title {
            font-size: 1.25rem;
        }
        
        .quiz-meta {
            flex-direction: column;
            gap: 0.625rem;
        }
        
        .quiz-page-nav {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .quiz-sidebar {
            position: static;
        }
    }
</style>
@endsection

@section('content')
<!-- Schema.org for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Quiz",
    "name": "{{ $quiz->title }}",
    "description": "{{ $description }}",
    "educationalLevel": "Ensino Superior - Direito",
    "about": {
        "@type": "Thing",
        "name": "{{ $quiz->category ? $quiz->category->name : 'Direito' }}"
    },
    "author": {
        "@type": "Organization",
        "name": "Teses & Súmulas",
        "url": "{{ url('/') }}"
    }
}
</script>

<!-- Breadcrumbs -->
<nav class="quiz-breadcrumb">
    <div class="quiz-breadcrumb-content">
        <ol class="breadcrumb-list">
            <li><a href="{{ url('/') }}"><i class="fa fa-home"></i> Início</a></li>
            <li class="separator"><i class="fa fa-chevron-right"></i></li>
            <li><a href="{{ route('quizzes.index') }}">Quizzes</a></li>
            @if($quiz->category)
                <li class="separator"><i class="fa fa-chevron-right"></i></li>
                <li><a href="{{ route('quizzes.category', $quiz->category->slug) }}">{{ $quiz->category->name }}</a></li>
            @endif
            <li class="separator"><i class="fa fa-chevron-right"></i></li>
            <li class="current">{{ Str::limit($quiz->title, 40) }}</li>
        </ol>
    </div>
</nav>

<!-- Main Content -->
<div class="quiz-page-main">
    <div class="quiz-layout">
        <!-- Quiz Container -->
        <div class="quiz-container">
            <div class="quiz-header">
                @if($quiz->category)
                    <span class="quiz-category">{{ $quiz->category->name }}</span>
                @endif
                <h1 class="quiz-title">{{ $quiz->title }}</h1>
                <div class="quiz-meta">
                    @if($quiz->tribunal)
                        <span><i class="fa fa-gavel"></i> {{ $quiz->tribunal }}{{ $quiz->tema_number ? ' - Tema ' . $quiz->tema_number : '' }}</span>
                    @endif
                    <span><i class="fa fa-clock-o"></i> ~{{ $quiz->estimated_time }} min</span>
                    <span><i class="fa fa-signal"></i> {{ $quiz->difficulty_label }}</span>
                </div>
            </div>
            
            @if($quiz->show_progress)
            <div class="progress-section">
                <div class="progress-info">
                    <span>Questão <strong id="currentQuestion">1</strong> de {{ $questions->count() }}</span>
                    <span id="progressPercent">0%</span>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" id="progressBar" style="width: 0%;"></div>
                </div>
            </div>
            @endif
            
            <div id="questionsContainer">
                @foreach($questions as $index => $question)
                    <div class="question-container" id="question-{{ $question->id }}" data-question-id="{{ $question->id }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
                        <div class="question-number">{{ $index + 1 }}</div>
                        <div class="question-text">{{ $question->text }}</div>
                        
                        <div class="options-list">
                            @foreach($question->options as $option)
                                <div class="option {{ in_array($question->id, $answeredIds) ? 'disabled' : '' }}" 
                                     data-option-id="{{ $option->id }}"
                                     onclick="selectOption(this, {{ $question->id }})">
                                    <span class="option-letter">{{ $option->letter }}</span>
                                    <span class="option-text">{{ $option->text }}</span>
                                    <span class="option-icon correct-icon"><i class="fa fa-check-circle"></i></span>
                                    <span class="option-icon incorrect-icon"><i class="fa fa-times-circle"></i></span>
                                </div>
                            @endforeach
                        </div>
                        
                        <button class="confirm-btn" id="confirmBtn-{{ $question->id }}" onclick="confirmAnswer({{ $question->id }})">
                            <i class="fa fa-check"></i> Confirmar Resposta
                        </button>
                        
                        <div class="feedback" id="feedback-{{ $question->id }}">
                            <div class="feedback-header">
                                <i class="fa fa-check-circle"></i>
                                <span id="feedbackTitle-{{ $question->id }}">Resposta Correta!</span>
                            </div>
                            <div class="feedback-text" id="feedbackText-{{ $question->id }}">
                                {{ $question->explanation }}
                            </div>
                        </div>
                        
                        <div class="quiz-navigation" id="navigation-{{ $question->id }}">
                            @if($index > 0)
                                <button class="nav-btn outline" onclick="showQuestion({{ $index - 1 }})">
                                    <i class="fa fa-arrow-left"></i> Anterior
                                </button>
                            @endif
                            @if($index < $questions->count() - 1)
                                <button class="nav-btn primary" onclick="showQuestion({{ $index + 1 }})">
                                    Próxima <i class="fa fa-arrow-right"></i>
                                </button>
                            @else
                                <a href="{{ route('quiz.results', $quiz->slug) }}" class="nav-btn primary" id="finishBtn" style="display: none;">
                                    Ver Resultado <i class="fa fa-trophy"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Sidebar -->
        <aside class="quiz-sidebar">
            @if($quiz->show_ads)
            <div class="sidebar-card">
                <div class="sidebar-card-body">
                    <div class="ad-space">
                        <small>Publicidade</small>
                        Espaço para Anúncio
                    </div>
                </div>
            </div>
            @endif
            
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <i class="fa fa-bar-chart"></i> Seu Progresso
                </div>
                <div class="sidebar-card-body">
                    <div class="progress-stats">
                        <div class="stat-item">
                            <div class="stat-number correct" id="statCorrect">0</div>
                            <div class="stat-label">Acertos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number wrong" id="statWrong">0</div>
                            <div class="stat-label">Erros</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="statTotal">0/{{ $questions->count() }}</div>
                            <div class="stat-label">Questões</div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($quiz->show_ads)
            <div class="sidebar-card">
                <div class="sidebar-card-body">
                    <div class="ad-space">
                        <small>Publicidade</small>
                        Espaço para Anúncio
                    </div>
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>
@endsection

@section('scripts')
<script>
const quizId = {{ $quiz->id }};
const attemptId = {{ $attempt->id }};
const totalQuestions = {{ $questions->count() }};
const showFeedbackImmediately = {{ $quiz->show_feedback_immediately ? 'true' : 'false' }};
const csrfToken = '{{ csrf_token() }}';

let currentQuestionIndex = 0;
let selectedOptions = {};
let correctCount = 0;
let wrongCount = 0;
let answeredCount = {{ count($answeredIds) }};

// Initialize from previous answers
@foreach($attempt->answers as $answer)
    selectedOptions[{{ $answer->question_id }}] = {{ $answer->selected_option_id ?? 'null' }};
    @if($answer->is_correct)
        correctCount++;
    @else
        wrongCount++;
    @endif
@endforeach

updateProgress();

function selectOption(element, questionId) {
    if (element.classList.contains('disabled')) return;
    
    // Deselect others
    document.querySelectorAll(`#question-${questionId} .option`).forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Select this one
    element.classList.add('selected');
    selectedOptions[questionId] = parseInt(element.dataset.optionId);
    
    // Show confirm button
    document.getElementById(`confirmBtn-${questionId}`).classList.add('show');
}

function confirmAnswer(questionId) {
    if (!selectedOptions[questionId]) return;
    
    const optionId = selectedOptions[questionId];
    const startTime = Date.now();
    
    // Disable options
    document.querySelectorAll(`#question-${questionId} .option`).forEach(opt => {
        opt.classList.add('disabled');
    });
    
    // Hide confirm button
    document.getElementById(`confirmBtn-${questionId}`).classList.remove('show');
    
    // Submit answer
    fetch(`/quiz/{{ $quiz->slug }}/answer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            attempt_id: attemptId,
            question_id: questionId,
            option_id: optionId,
            time_spent: Math.round((Date.now() - startTime) / 1000)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            answeredCount++;
            
            // Mark correct/incorrect
            const selectedOption = document.querySelector(`#question-${questionId} .option[data-option-id="${optionId}"]`);
            
            if (data.is_correct) {
                selectedOption.classList.add('correct');
                correctCount++;
            } else {
                selectedOption.classList.add('incorrect');
                wrongCount++;
                
                // Highlight correct answer
                if (data.correct_option) {
                    const correctOption = document.querySelector(`#question-${questionId} .option[data-option-id="${data.correct_option.id}"]`);
                    if (correctOption) {
                        correctOption.classList.add('correct');
                    }
                }
            }
            
            // Show feedback if configured
            if (showFeedbackImmediately) {
                const feedback = document.getElementById(`feedback-${questionId}`);
                const feedbackTitle = document.getElementById(`feedbackTitle-${questionId}`);
                
                if (data.is_correct) {
                    feedbackTitle.innerHTML = '<i class="fa fa-check-circle"></i> Resposta Correta!';
                } else {
                    feedback.classList.add('incorrect');
                    feedbackTitle.innerHTML = '<i class="fa fa-times-circle"></i> Resposta Incorreta';
                }
                
                feedback.classList.add('show');
            }
            
            // Show navigation
            document.getElementById(`navigation-${questionId}`).classList.add('show');
            
            // Update stats
            updateProgress();
            
            // Show finish button if complete
            if (data.is_complete) {
                const finishBtn = document.getElementById('finishBtn');
                if (finishBtn) {
                    finishBtn.style.display = 'flex';
                }
            }
        }
    });
}

function showQuestion(index) {
    const questions = document.querySelectorAll('.question-container');
    questions.forEach((q, i) => {
        q.style.display = i === index ? 'block' : 'none';
    });
    currentQuestionIndex = index;
    document.getElementById('currentQuestion').textContent = index + 1;
}

function updateProgress() {
    const percent = Math.round((answeredCount / totalQuestions) * 100);
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    
    if (progressBar) progressBar.style.width = `${percent}%`;
    if (progressPercent) progressPercent.textContent = `${percent}%`;
    
    document.getElementById('statCorrect').textContent = correctCount;
    document.getElementById('statWrong').textContent = wrongCount;
    document.getElementById('statTotal').textContent = `${answeredCount}/${totalQuestions}`;
}
</script>
@endsection
