@extends('layouts.user-panel')

@section('panel-title', 'Visão Geral')

@section('panel-styles')
<style>
    .dashboard-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        padding: 24px;
        margin-bottom: 20px;
    }
    .dashboard-card h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 20px 0;
        color: #212529;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.9375rem;
    }
    .status-item > span:first-child { flex: 1 1 0; min-width: 0; }
    .status-item .status-right { flex-shrink: 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .status-item .status-right form { display: inline-flex; margin: 0; }
    .status-item .status-right a { color: #0d6efd; font-weight: 500; text-decoration: none; }
    .status-item .status-right a:hover { text-decoration: underline; }
    .status-item:last-child { border-bottom: none; }
    .status-item span:first-child { color: #212529; font-weight: 500; }
    .status-ok { color: #198754; font-weight: 600; }
    .status-pending { color: #fd7e14; font-weight: 600; }
    .status-none { color: #6c757d; }
    .quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }
    .quick-actions a {
        padding: 10px 18px;
        background: #0d6efd;
        color: #fff !important;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.15s ease;
    }
    .quick-actions a:hover {
        background: #0b5ed7;
        color: #fff !important;
    }
    .status-item .btn-link, .status-item button.btn-link {
        padding: 0;
        font-size: 0.875rem;
        font-weight: 500;
        color: #0d6efd;
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
        margin-left: 8px;
    }
    .status-item .btn-link:hover, .status-item button.btn-link:hover {
        text-decoration: underline;
        color: #0a58ca;
    }
</style>
@endsection

@section('panel-content')
<div class="dashboard-card">
    <h3>Status da conta</h3>
    <div class="status-item">
        <span>E-mail verificado</span>
        <span class="status-right">
            @if(auth()->user()->hasVerifiedEmail())
                <span class="status-ok">✓ Sim</span>
            @else
                <span class="status-pending">✗ Pendente</span>
                <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link">Reenviar verificação</button>
                </form>
            @endif
        </span>
    </div>
    <div class="status-item">
        <span>Autenticação em dois fatores</span>
        <span class="status-right">
            @if(auth()->user()->two_factor_confirmed_at)
                <span class="status-ok">✓ Ativo</span>
            @else
                <span class="status-none">Não configurado</span>
                <a href="{{ route('user-panel.profile') }}#2fa">Configurar</a>
            @endif
        </span>
    </div>
    <div class="status-item">
        <span>Assinatura</span>
        <span class="status-right">
        @if(auth()->user()->isSubscriber())
            @if(auth()->user()->isOnGracePeriod())
                <span class="status-pending">Em carência</span>
            @else
                <span class="status-ok">Ativa</span>
            @endif
        @else
            <span class="status-none">Sem assinatura</span>
        @endif
        </span>
    </div>
</div>

<div class="dashboard-card">
    <h3>Ações rápidas</h3>
    <div class="quick-actions">
        <a href="{{ route('user-panel.profile') }}">Editar perfil</a>
        @if(auth()->user()->isSubscriber())
            <a href="{{ route('subscription.portal') }}">Gerenciar assinatura</a>
            <a href="{{ route('refund.create') }}">Solicitar estorno</a>
        @else
            <a href="{{ route('subscription.plans') }}">Ver planos</a>
        @endif
    </div>
</div>
@endsection
