@extends('front.base')

@section('page-title', isset($category) ? 'Quizzes de ' . $category->name : 'Quizzes Jurídicos')

@section('styles')
<style>
    /* ========================================
       QUIZ LISTING PAGE - SITE CONSISTENT
       ======================================== */
    
    :root {
        --primary-color: #5c80d1;
        --primary-hover: #4a6bb8;
        --text-color: #575757;
        --text-muted: #6c757d;
        --bg-light: #f5f5f5;
        --border-color: #ebebeb;
    }
    
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
        color: var(--primary-hover);
        text-decoration: underline;
    }
    
    .breadcrumb-list .separator {
        color: #adb5bd;
        font-size: 0.75rem;
    }
    
    .breadcrumb-list .current {
        color: var(--text-muted);
    }
    
    /* Page Header */
    .quiz-page-header {
        background: white;
        padding: 2rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .quiz-page-header-content {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    .quiz-page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--primary-color);
        margin: 0 0 0.5rem 0;
    }
    
    .quiz-page-subtitle {
        font-size: 1rem;
        color: var(--text-muted);
        margin: 0;
        max-width: 700px;
        line-height: 1.6;
    }
    
    /* Main Content Layout */
    .quiz-main-content {
        max-width: 1100px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
    }
    
    .quiz-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 1.5rem;
    }
    
    @media (max-width: 991px) {
        .quiz-layout {
            grid-template-columns: 1fr;
        }
    }
    
    /* Sidebar / Filters */
    .quiz-sidebar {
        position: sticky;
        top: 1rem;
        height: fit-content;
    }
    
    .filter-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    
    .filter-card-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9375rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-color);
    }
    
    .filter-card-title i {
        color: var(--primary-color);
    }
    
    .filter-section-title {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin: 1rem 0 0.5rem;
        padding-bottom: 0.375rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .filter-section-title:first-child {
        margin-top: 0;
    }
    
    .filter-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .filter-list li {
        margin-bottom: 0.125rem;
    }
    
    .filter-list a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.625rem;
        border-radius: 4px;
        color: var(--text-color);
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.15s ease;
    }
    
    .filter-list a:hover {
        background: var(--bg-light);
        color: var(--primary-color);
    }
    
    .filter-list a.active {
        background: var(--primary-color);
        color: white;
    }
    
    .filter-count {
        background: var(--border-color);
        padding: 0.125rem 0.5rem;
        border-radius: 50px;
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--text-muted);
    }
    
    .filter-list a.active .filter-count {
        background: rgba(255,255,255,0.3);
        color: white;
    }
    
    .difficulty-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.375rem;
    }
    
    .difficulty-dot.easy { background: #10b981; }
    .difficulty-dot.medium { background: #f59e0b; }
    .difficulty-dot.hard { background: #ef4444; }
    
    /* Quiz Grid */
    .quiz-grid-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .quiz-grid-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-color);
        margin: 0;
    }
    
    .quiz-grid-count {
        font-size: 0.8125rem;
        color: var(--text-muted);
    }
    
    .quiz-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }
    
    /* Quiz Cards */
    .quiz-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .quiz-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(92, 128, 209, 0.15);
    }
    
    .quiz-card-header {
        padding: 1.25rem;
        color: white;
    }
    
    .quiz-card-category {
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
    
    .quiz-card-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.4;
    }
    
    .quiz-card-body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .quiz-card-description {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-bottom: 1rem;
        line-height: 1.5;
        flex: 1;
    }
    
    .quiz-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }
    
    .quiz-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    
    .quiz-meta-item i {
        font-size: 0.75rem;
    }
    
    .quiz-meta-item.difficulty-easy { color: #10b981; }
    .quiz-meta-item.difficulty-medium { color: #f59e0b; }
    .quiz-meta-item.difficulty-hard { color: #ef4444; }
    
    .quiz-card-footer {
        padding: 0.75rem 1.25rem 1.25rem;
    }
    
    .quiz-start-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        width: 100%;
        padding: 0.625rem 1rem;
        border: none;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 600;
        color: white;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .quiz-start-btn:hover {
        filter: brightness(1.1);
        color: white;
        text-decoration: none;
    }
    
    .quiz-start-btn i {
        font-size: 0.75rem;
    }
    
    /* Empty State */
    .quiz-empty-state {
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
    }
    
    .quiz-empty-state i {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .quiz-empty-state h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }
    
    .quiz-empty-state p {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }
    
    /* Mobile Adjustments */
    @media (max-width: 767px) {
        .quiz-grid {
            grid-template-columns: 1fr;
        }
        
        .quiz-sidebar {
            position: static;
        }
    }
</style>
@endsection

@section('content')
<!-- Breadcrumbs -->
<nav class="quiz-breadcrumb">
    <div class="quiz-breadcrumb-content">
        <ol class="breadcrumb-list">
            <li><a href="{{ url('/') }}"><i class="fa fa-home"></i> Início</a></li>
            <li class="separator"><i class="fa fa-chevron-right"></i></li>
            @if(isset($category))
                <li><a href="{{ route('quizzes.index') }}">Quizzes</a></li>
                <li class="separator"><i class="fa fa-chevron-right"></i></li>
                <li class="current">{{ $category->name }}</li>
            @else
                <li class="current">Quizzes Jurídicos</li>
            @endif
        </ol>
    </div>
</nav>

<!-- Page Header -->
<div class="quiz-page-header">
    <div class="quiz-page-header-content">
        <h1 class="quiz-page-title">
            @if(isset($category))
                Quizzes de {{ $category->name }}
            @else
                Quizzes Jurídicos
            @endif
        </h1>
        <p class="quiz-page-subtitle">
            Teste seus conhecimentos sobre jurisprudência vinculante do STF e STJ. 
            Cada quiz possui questões baseadas em teses de repercussão geral e recursos repetitivos.
        </p>
    </div>
</div>

<!-- Main Content -->
<div class="quiz-main-content">
    <div class="quiz-layout">
        <!-- Sidebar -->
        <aside class="quiz-sidebar">
            <div class="filter-card">
                <h3 class="filter-card-title">
                    <i class="fa fa-filter"></i> Filtros
                </h3>
                
                <h4 class="filter-section-title">Categoria</h4>
                <ul class="filter-list">
                    <li>
                        <a href="{{ route('quizzes.index') }}" class="{{ !isset($category) && !request('tribunal') && !request('dificuldade') ? 'active' : '' }}">
                            <span>Todas as categorias</span>
                        </a>
                    </li>
                    @foreach($categories as $cat)
                        @if($cat->quizzes_count > 0)
                        <li>
                            <a href="{{ route('quizzes.category', $cat->slug) }}" class="{{ isset($category) && $category->id == $cat->id ? 'active' : '' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="filter-count">{{ $cat->quizzes_count }}</span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                </ul>
                
                <h4 class="filter-section-title">Tribunal</h4>
                <ul class="filter-list">
                    @foreach(['STF', 'STJ', 'TST', 'TNU'] as $tribunal)
                    <li>
                        <a href="{{ route('quizzes.index', array_merge(request()->except('tribunal'), ['tribunal' => $tribunal])) }}" 
                           class="{{ request('tribunal') == $tribunal ? 'active' : '' }}">
                            <span>{{ $tribunal }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                
                <h4 class="filter-section-title">Dificuldade</h4>
                <ul class="filter-list">
                    <li>
                        <a href="{{ route('quizzes.index', array_merge(request()->except('dificuldade'), ['dificuldade' => 'easy'])) }}" 
                           class="{{ request('dificuldade') == 'easy' ? 'active' : '' }}">
                            <span><span class="difficulty-dot easy"></span> Fácil</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('quizzes.index', array_merge(request()->except('dificuldade'), ['dificuldade' => 'medium'])) }}" 
                           class="{{ request('dificuldade') == 'medium' ? 'active' : '' }}">
                            <span><span class="difficulty-dot medium"></span> Intermediário</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('quizzes.index', array_merge(request()->except('dificuldade'), ['dificuldade' => 'hard'])) }}" 
                           class="{{ request('dificuldade') == 'hard' ? 'active' : '' }}">
                            <span><span class="difficulty-dot hard"></span> Difícil</span>
                        </a>
                    </li>
                </ul>
                
                @if(request()->hasAny(['tribunal', 'dificuldade']) || isset($category))
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e9ecef;">
                    <a href="{{ route('quizzes.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fa fa-times"></i> Limpar Filtros
                    </a>
                </div>
                @endif
            </div>
        </aside>
        
        <!-- Quiz List -->
        <main class="quiz-content">
            <div class="quiz-grid-header">
                <h2 class="quiz-grid-title">
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
                <span class="quiz-grid-count">
                    {{ $quizzes->total() }} quiz{{ $quizzes->total() != 1 ? 'zes' : '' }} encontrado{{ $quizzes->total() != 1 ? 's' : '' }}
                </span>
            </div>
            
            @if($quizzes->count() > 0)
                <div class="quiz-grid">
                    @foreach($quizzes as $quiz)
                        <article class="quiz-card">
                            <div class="quiz-card-header" style="background-color: #5c80d1;">
                                @if($quiz->category)
                                    <span class="quiz-card-category">{{ $quiz->category->name }}</span>
                                @endif
                                <h3 class="quiz-card-title">{{ $quiz->title }}</h3>
                            </div>
                            <div class="quiz-card-body">
                                <p class="quiz-card-description">
                                    {{ Str::limit($quiz->description, 120) }}
                                </p>
                                <div class="quiz-card-meta">
                                    @if($quiz->tribunal)
                                        <span class="quiz-meta-item">
                                            <i class="fa fa-gavel"></i> {{ $quiz->tribunal }}
                                        </span>
                                    @endif
                                    <span class="quiz-meta-item">
                                        <i class="fa fa-list-ol"></i> {{ $quiz->questions_count }} questões
                                    </span>
                                    <span class="quiz-meta-item">
                                        <i class="fa fa-clock-o"></i> ~{{ $quiz->estimated_time }} min
                                    </span>
                                    <span class="quiz-meta-item difficulty-{{ $quiz->difficulty }}">
                                        <i class="fa fa-signal"></i> {{ $quiz->difficulty_label }}
                                    </span>
                                </div>
                            </div>
                            <div class="quiz-card-footer">
                                <a href="{{ route('quiz.show', $quiz->slug) }}" class="quiz-start-btn" style="background-color: #5c80d1;">
                                    <i class="fa fa-play"></i> Iniciar Quiz
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($quizzes->hasPages())
                <div class="quiz-pagination">
                    {{ $quizzes->appends(request()->query())->links() }}
                </div>
                @endif
            @else
                <div class="quiz-empty-state">
                    <i class="fa fa-graduation-cap"></i>
                    <h4>Nenhum quiz encontrado</h4>
                    <p>Não encontramos quizzes com os filtros selecionados. Tente ajustar sua busca.</p>
                    <a href="{{ route('quizzes.index') }}" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> Ver todos os quizzes
                    </a>
                </div>
            @endif
        </main>
    </div>
</div>
@endsection
