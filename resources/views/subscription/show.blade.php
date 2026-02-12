@extends('layouts.user-panel')

@section('panel-title', 'Minha Assinatura')

@section('panel-styles')
<style>
    .subscription-container { margin: 0; }
    .subscription-header { margin-bottom: 24px; }
    .subscription-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
    }
    .subscription-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        padding: 24px;
        margin-bottom: 20px;
    }
    .subscription-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
        margin: 0 0 16px 0;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .status-active { background: #d1e7dd; color: #0f5132; }
    .status-grace { background: #fff3cd; color: #664d03; }
    .status-inactive { background: #f8d7da; color: #842029; }
    .plan-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.9375rem;
    }
    .plan-info:last-child { border-bottom: none; }
    .plan-label { color: #6c757d; font-weight: 500; }
    .plan-value { font-weight: 600; color: #212529; }
    .grace-warning {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 6px;
        padding: 16px 20px;
        margin-bottom: 20px;
        color: #664d03;
        font-size: 0.9375rem;
    }
    .btn-group { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
    .btn-primary {
        padding: 12px 20px;
        background: #0d6efd;
        color: #fff !important;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9375rem;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .btn-primary:hover {
        background: #0b5ed7;
        color: #fff !important;
        text-decoration: none;
    }
    .link-small {
        color: #0d6efd;
        font-size: 0.875rem;
        text-decoration: none;
        font-weight: 500;
    }
    .link-small:hover { text-decoration: underline; color: #0a58ca; }
    .subscription-card .alert { padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; }
    .alert-success { background: #d1e7dd; color: #0f5132; }
    .alert-info { background: #cff4fc; color: #055160; }
    .no-subscription { text-align: center; padding: 48px 20px; }
    .no-subscription h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 12px;
    }
    .no-subscription p {
        color: #6c757d;
        margin-bottom: 24px;
        font-size: 0.9375rem;
    }
    .benefits-list {
        margin: 16px 0 0;
        padding-left: 1.25rem;
        color: #212529;
        font-size: 0.9375rem;
        line-height: 1.7;
    }
    .benefits-list li { margin-bottom: 6px; }
</style>
@endsection

@section('panel-content')
<div class="subscription-container">
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    
    @if($isSubscriber)
        @if($isOnGracePeriod)
        <div class="grace-warning">
            <strong>⚠️ Assinatura cancelada</strong><br>
            Você ainda tem acesso até {{ $accessEndsAt->format('d/m/Y') }}. Após essa data, sua assinatura será encerrada.
        </div>
        @endif
        
        <div class="subscription-card">
            <div class="plan-info">
                <span class="plan-label">Status</span>
                <span class="status-badge {{ $isOnGracePeriod ? 'status-grace' : 'status-active' }}">
                    {{ $isOnGracePeriod ? 'Em período de carência' : 'Ativa' }}
                </span>
            </div>
            
            @if($planName)
            <div class="plan-info">
                <span class="plan-label">Plano</span>
                <span class="plan-value">{{ strtoupper($planName) }}</span>
            </div>
            @endif
            
            @if($subscription)
            <div class="plan-info">
                <span class="plan-label">Assinante desde</span>
                <span class="plan-value">{{ $subscription->created_at->format('d/m/Y') }}</span>
            </div>
            
            @if($subscription->current_period_end && !$isOnGracePeriod)
            <div class="plan-info">
                <span class="plan-label">Próxima renovação</span>
                <span class="plan-value">{{ \Carbon\Carbon::parse($subscription->current_period_end)->format('d/m/Y') }}</span>
            </div>
            @endif
            @endif
            
            <div class="btn-group">
                <a href="{{ route('subscription.portal') }}" class="btn-primary">
                    Gerenciar Assinatura
                </a>
            </div>
            
            <p class="text-muted mt-3" style="font-size: 0.875rem; color: #6c757d;">
                No portal, você pode atualizar seu cartão, trocar de plano ou cancelar.
            </p>
            <div class="mt-2">
                <a href="{{ route('refund.create') }}" class="link-small">Solicitar estorno</a>
            </div>
        </div>
        
        <div class="subscription-card">
            <h3>Seus Benefícios</h3>
            <ul class="benefits-list">
                @if($user->hasFeature('no_ads'))
                <li>Navegação sem anúncios</li>
                @endif
                @if($user->hasFeature('exclusive_content'))
                <li>Acesso a conteúdo exclusivo</li>
                @endif
                @if($user->hasFeature('ai_tools'))
                <li>Ferramentas de IA</li>
                @endif
            </ul>
        </div>
    @else
        <div class="subscription-card no-subscription">
            <h2>Você não possui uma assinatura ativa</h2>
            <p>Assine agora para navegar sem anúncios e acessar conteúdo exclusivo.</p>
            <a href="{{ route('subscription.plans') }}" class="btn-primary">
                Ver Planos
            </a>
        </div>
    @endif
</div>
@endsection
