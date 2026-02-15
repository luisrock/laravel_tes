@extends('layouts.admin')

@section('content')
<div class="tw-max-w-5xl tw-mx-auto">
    
    <!-- Header -->
    <div class="tw-mb-8">
        <h2 class="tw-text-2xl tw-font-bold tw-text-slate-900">Painel Administrativo</h2>
        <p class="tw-text-slate-600">Bem-vindo, {{ Auth::user()->name }}. Escolha uma área para gerenciar.</p>
    </div>

    <!-- Quick Links -->
    <div class="tw-flex tw-flex-wrap tw-gap-3 tw-mb-8">
        <a href="{{ route('searchpage') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-slate-100 tw-text-slate-700 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-brand-600 hover:tw-text-white tw-transition-colors text-decoration-none">
            <i class="fas fa-search tw-mr-2"></i> Pesquisar
        </a>
        <a href="{{ route('alltemaspage') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-slate-100 tw-text-slate-700 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-brand-600 hover:tw-text-white tw-transition-colors text-decoration-none">
            <i class="fas fa-list tw-mr-2"></i> Ver Temas
        </a>
        <a href="{{ url('/') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-slate-100 tw-text-slate-700 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-brand-600 hover:tw-text-white tw-transition-colors text-decoration-none">
            <i class="fas fa-home tw-mr-2"></i> Home do Site
        </a>
        <a href="{{ route('quizzes.index') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-slate-100 tw-text-slate-700 tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-brand-600 hover:tw-text-white tw-transition-colors text-decoration-none">
            <i class="fas fa-graduation-cap tw-mr-2"></i> Ver Quizzes (público)
        </a>
    </div>

    <!-- Main Content Sections -->
    <div class="tw-mb-10">
        <h3 class="tw-text-sm tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4">Gestão de Conteúdo</h3>
        
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            
            <!-- Temas/Pesquisas -->
            <a href="{{ route('admin.temas') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-blue-50 tw-text-brand-600">
                    <i class="fas fa-book tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Temas & Pesquisas</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Gerencie os temas de pesquisa, crie páginas e verifique status.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $stats['total'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Total</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $stats['created'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Criados</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $stats['pending'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Pendentes</div>
                    </div>
                </div>
            </a>

            <!-- Quizzes -->
            <a href="{{ route('admin.quizzes.index') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-green-50 tw-text-green-600">
                    <i class="fas fa-graduation-cap tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Quizzes</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Crie e gerencie quizzes para testar conhecimentos dos visitantes.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $quizStats['total'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Quizzes</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $quizStats['published'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Publicados</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $quizStats['questions'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Perguntas</div>
                    </div>
                </div>
            </a>

            <!-- Perguntas -->
            <a href="{{ route('admin.questions.index') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-purple-50 tw-text-purple-600">
                    <i class="fas fa-question-circle tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Banco de Perguntas</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Gerencie perguntas que podem ser usadas em múltiplos quizzes.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $quizStats['questions'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Total</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $quizStats['categories'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Categorias</div>
                    </div>
                </div>
            </a>

            <!-- Estatísticas de Quizzes -->
            <a href="{{ route('admin.quizzes.stats') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-orange-50 tw-text-orange-600">
                    <i class="fas fa-chart-bar tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Estatísticas de Quizzes</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Acompanhe desempenho, tentativas e análises dos quizzes.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $quizStats['attempts'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Tentativas</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $quizStats['completed'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Concluídas</div>
                    </div>
                </div>
            </a>

            <!-- Acórdãos -->
            <a href="{{ route('admin.acordaos.index') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-teal-50 tw-text-teal-600">
                    <i class="fas fa-file-pdf tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Análise do Precedente</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Faça upload de acórdãos (PDFs) das teses STF/STJ para análise com IA.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $acordaosStats['total'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Total</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $acordaosStats['stf'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">STF</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $acordaosStats['stj'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">STJ</div>
                    </div>
                </div>
            </a>

        </div>
    </div>

    <!-- Gestão de Usuários -->
    <div class="tw-mb-10">
        <h3 class="tw-text-sm tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4">Gestão de Usuários</h3>
        
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            
            <!-- Usuários -->
            <a href="{{ route('users.index') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-blue-50 tw-text-blue-600">
                    <i class="fas fa-users tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Usuários do Sistema</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Gerencie contas de usuários, permissões e acessos.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $userStats['total'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Total</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $userStats['admins'] }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Admins</div>
                    </div>
                </div>
            </a>

            <!-- Roles -->
            <a href="{{ route('roles.index') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-indigo-50 tw-text-indigo-600">
                    <i class="fas fa-user-tag tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Roles (Papéis)</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Gerencie os papéis e níveis de acesso dos usuários.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $userStats['roles'] ?? '-' }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Total</div>
                    </div>
                </div>
            </a>

            <!-- Permissions -->
            <a href="{{ route('permissions.index') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-pink-50 tw-text-pink-600">
                    <i class="fas fa-key tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Permissões</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Gerencie as permissões granulares do sistema.
                </div>
                <div class="tw-flex tw-gap-4 tw-pt-4 tw-border-t tw-border-slate-100">
                    <div class="tw-text-center">
                        <div class="tw-text-lg tw-font-bold tw-text-slate-900">{{ $userStats['permissions'] ?? '-' }}</div>
                        <div class="tw-text-xs tw-text-slate-400 tw-uppercase">Total</div>
                    </div>
                </div>
            </a>

        </div>
    </div>

    <!-- Other Admin Sections -->
    <div class="tw-mb-10">
        <h3 class="tw-text-sm tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4">Outras Áreas</h3>
        
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            
            <!-- Newsletters -->
            <a href="{{ route('newsletterspage') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-teal-50 tw-text-teal-600">
                    <i class="fas fa-envelope tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Newsletters</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Visualize as atualizações e newsletters publicadas.
                </div>
            </a>

            <!-- Tags de Perguntas -->
            <a href="{{ route('admin.questions.tags') }}" class="tw-block tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md hover:tw-border-brand-500 tw-transition-all tw-duration-200 text-decoration-none hover:tw-translate-y-[-2px]">
                <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-4 tw-bg-red-50 tw-text-red-600">
                    <i class="fas fa-tags tw-text-xl"></i>
                </div>
                <div class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">Tags de Perguntas</div>
                <div class="tw-text-sm tw-text-slate-500 tw-mb-4 tw-leading-relaxed">
                    Gerencie as tags usadas para categorizar perguntas.
                </div>
            </a>

        </div>
    </div>

</div>
@endsection
