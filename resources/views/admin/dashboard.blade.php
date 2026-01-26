@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }
    .dashboard-header h2 {
        margin-bottom: 0.5rem;
    }
    .dashboard-header p {
        color: #6c757d;
        margin: 0;
    }
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    .dashboard-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        display: block;
        border: 2px solid transparent;
    }
    .dashboard-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        border-color: #5c80d1;
        text-decoration: none;
        color: inherit;
        transform: translateY(-2px);
    }
    .dashboard-card-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .dashboard-card-icon.blue { background: #e8f0fe; color: #5c80d1; }
    .dashboard-card-icon.green { background: #d1fae5; color: #10b981; }
    .dashboard-card-icon.purple { background: #ede9fe; color: #8b5cf6; }
    .dashboard-card-icon.orange { background: #ffedd5; color: #f97316; }
    .dashboard-card-icon.red { background: #fee2e2; color: #ef4444; }
    .dashboard-card-icon.teal { background: #ccfbf1; color: #14b8a6; }
    .dashboard-card-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #1f2937;
    }
    .dashboard-card-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    .dashboard-card-stats {
        display: flex;
        gap: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    .dashboard-card-stat {
        text-align: center;
    }
    .dashboard-card-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
    }
    .dashboard-card-stat-label {
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
    }
    .dashboard-section {
        margin-bottom: 2rem;
    }
    .dashboard-section-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
    }
    .quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }
    .quick-link {
        padding: 0.5rem 1rem;
        background: #f3f4f6;
        border-radius: 6px;
        color: #374151;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.15s;
    }
    .quick-link:hover {
        background: #5c80d1;
        color: white;
        text-decoration: none;
    }
    .quick-link i {
        margin-right: 0.375rem;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            
            <!-- Header -->
            <div class="dashboard-header">
                <h2>Painel Administrativo</h2>
                <p>Bem-vindo, {{ Auth::user()->name }}. Escolha uma área para gerenciar.</p>
            </div>

            <!-- Quick Links -->
            <div class="quick-links">
                <a href="{{ route('searchpage') }}" class="quick-link">
                    <i class="fas fa-search"></i> Pesquisar
                </a>
                <a href="{{ route('alltemaspage') }}" class="quick-link">
                    <i class="fas fa-list"></i> Ver Temas
                </a>
                <a href="{{ url('/') }}" class="quick-link">
                    <i class="fas fa-home"></i> Home do Site
                </a>
                <a href="{{ route('quizzes.index') }}" class="quick-link">
                    <i class="fas fa-graduation-cap"></i> Ver Quizzes (público)
                </a>
            </div>

            <!-- Main Content Sections -->
            <div class="dashboard-section" style="margin-top: 2rem;">
                <h3 class="dashboard-section-title">Gestão de Conteúdo</h3>
                <div class="dashboard-grid">
                    
                    <!-- Temas/Pesquisas -->
                    <a href="{{ route('admin.temas') }}" class="dashboard-card">
                        <div class="dashboard-card-icon blue">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="dashboard-card-title">Temas & Pesquisas</div>
                        <div class="dashboard-card-description">
                            Gerencie os temas de pesquisa, crie páginas e verifique status.
                        </div>
                        <div class="dashboard-card-stats">
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $stats['total'] }}</div>
                                <div class="dashboard-card-stat-label">Total</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $stats['created'] }}</div>
                                <div class="dashboard-card-stat-label">Criados</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $stats['pending'] }}</div>
                                <div class="dashboard-card-stat-label">Pendentes</div>
                            </div>
                        </div>
                    </a>

                    <!-- Quizzes -->
                    <a href="{{ route('admin.quizzes.index') }}" class="dashboard-card">
                        <div class="dashboard-card-icon green">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="dashboard-card-title">Quizzes</div>
                        <div class="dashboard-card-description">
                            Crie e gerencie quizzes para testar conhecimentos dos visitantes.
                        </div>
                        <div class="dashboard-card-stats">
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $quizStats['total'] }}</div>
                                <div class="dashboard-card-stat-label">Quizzes</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $quizStats['published'] }}</div>
                                <div class="dashboard-card-stat-label">Publicados</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $quizStats['questions'] }}</div>
                                <div class="dashboard-card-stat-label">Perguntas</div>
                            </div>
                        </div>
                    </a>

                    <!-- Perguntas -->
                    <a href="{{ route('admin.questions.index') }}" class="dashboard-card">
                        <div class="dashboard-card-icon purple">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="dashboard-card-title">Banco de Perguntas</div>
                        <div class="dashboard-card-description">
                            Gerencie perguntas que podem ser usadas em múltiplos quizzes.
                        </div>
                        <div class="dashboard-card-stats">
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $quizStats['questions'] }}</div>
                                <div class="dashboard-card-stat-label">Total</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $quizStats['categories'] }}</div>
                                <div class="dashboard-card-stat-label">Categorias</div>
                            </div>
                        </div>
                    </a>

                    <!-- Estatísticas de Quizzes -->
                    <a href="{{ route('admin.quizzes.stats') }}" class="dashboard-card">
                        <div class="dashboard-card-icon orange">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="dashboard-card-title">Estatísticas de Quizzes</div>
                        <div class="dashboard-card-description">
                            Acompanhe desempenho, tentativas e análises dos quizzes.
                        </div>
                        <div class="dashboard-card-stats">
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $quizStats['attempts'] }}</div>
                                <div class="dashboard-card-stat-label">Tentativas</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $quizStats['completed'] }}</div>
                                <div class="dashboard-card-stat-label">Concluídas</div>
                            </div>
                        </div>
                    </a>

                    <!-- Acórdãos -->
                    <a href="{{ route('admin.acordaos.index') }}" class="dashboard-card">
                        <div class="dashboard-card-icon teal">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="dashboard-card-title">Análise do Precedente</div>
                        <div class="dashboard-card-description">
                            Faça upload de acórdãos (PDFs) das teses STF/STJ para análise com IA.
                        </div>
                        <div class="dashboard-card-stats">
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $acordaosStats['total'] }}</div>
                                <div class="dashboard-card-stat-label">Total</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $acordaosStats['stf'] }}</div>
                                <div class="dashboard-card-stat-label">STF</div>
                            </div>
                            <div class="dashboard-card-stat">
                                <div class="dashboard-card-stat-value">{{ $acordaosStats['stj'] }}</div>
                                <div class="dashboard-card-stat-label">STJ</div>
                            </div>
                        </div>
                    </a>

                </div>
            </div>

            <!-- Other Admin Sections -->
            <div class="dashboard-section">
                <h3 class="dashboard-section-title">Outras Áreas</h3>
                <div class="dashboard-grid">
                    
                    <!-- Newsletters -->
                    <a href="{{ route('newsletterspage') }}" class="dashboard-card">
                        <div class="dashboard-card-icon teal">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="dashboard-card-title">Newsletters</div>
                        <div class="dashboard-card-description">
                            Visualize as atualizações e newsletters publicadas.
                        </div>
                    </a>

                    <!-- Tags de Perguntas -->
                    <a href="{{ route('admin.questions.tags') }}" class="dashboard-card">
                        <div class="dashboard-card-icon red">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="dashboard-card-title">Tags de Perguntas</div>
                        <div class="dashboard-card-description">
                            Gerencie as tags usadas para categorizar perguntas.
                        </div>
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
