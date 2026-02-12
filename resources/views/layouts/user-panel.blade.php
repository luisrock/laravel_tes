@extends('front.base', ['display_pdf' => false])

@section('page-title', $pageTitle ?? 'Minha Conta')

@section('styles')
<style>
    /* Painel: tipografia e cores profissionais, sem gradientes */
    .user-panel-layout {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 24px;
        width: 100%;
        font-family: inherit;
    }
    .user-panel-header {
        margin-bottom: 24px;
    }
    .user-panel-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }
    .back-to-site {
        display: inline-block;
        margin-top: 24px;
        color: #0d6efd;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .back-to-site:hover {
        text-decoration: underline;
        color: #0a58ca;
    }
    .user-panel-content .text-muted { color: #6c757d !important; }
</style>
@yield('panel-styles')
@endsection

@section('content')
<div class="user-panel-wrapper" style="min-height: calc(100vh - 200px); display: flex; flex-direction: column; justify-content: center; padding: 60px 0;">
<div class="user-panel-layout">
    <main class="user-panel-content">
        <div class="user-panel-header">
            <h1>@yield('panel-title', $pageTitle ?? 'Minha Conta')</h1>
        </div>
        @yield('panel-content')
        <a href="{{ route('searchpage') }}" class="back-to-site">← Voltar ao site</a>
    </main>
</div>
</div>
@yield('panel-scripts')
@endsection
