@extends('front.base', ['display_pdf' => false])

@section('page-title', 'Minha Assinatura')

@section('styles')
<style>
    .subscription-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .subscription-header {
        margin-bottom: 30px;
    }
    .subscription-header h1 {
        font-size: 2rem;
        color: #333;
    }
    
    .subscription-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 30px;
        margin-bottom: 20px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    .status-grace {
        background: #fff3cd;
        color: #856404;
    }
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .plan-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid #eee;
    }
    .plan-info:last-child {
        border-bottom: none;
    }
    .plan-label {
        color: #666;
        font-size: 0.95rem;
    }
    .plan-value {
        font-weight: 600;
        color: #333;
    }
    
    .grace-warning {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
        color: #856404;
    }
    
    .btn-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .btn-primary {
        padding: 12px 25px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity 0.2s ease;
    }
    .btn-primary:hover {
        opacity: 0.9;
        color: white;
        text-decoration: none;
    }
    
    .btn-outline {
        padding: 12px 25px;
        background: transparent;
        color: #667eea;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        border: 2px solid #667eea;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-outline:hover {
        background: #667eea;
        color: white;
        text-decoration: none;
    }
    
    .btn-danger-outline {
        padding: 12px 25px;
        background: transparent;
        color: #dc3545;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        border: 2px solid #dc3545;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-danger-outline:hover {
        background: #dc3545;
        color: white;
        text-decoration: none;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
    }
    .alert-info {
        background: #e7f3ff;
        color: #0066cc;
    }
    
    .no-subscription {
        text-align: center;
        padding: 60px 20px;
    }
    .no-subscription h2 {
        color: #333;
        margin-bottom: 15px;
    }
    .no-subscription p {
        color: #666;
        margin-bottom: 25px;
    }
</style>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-sm-fill h3 my-2">
                <a href="{{ url('/') }}">
                Teses & Súmulas
                </a>
            </h1>
            <span>
                <a href="{{ route('searchpage') }}" class="badge badge-primary">Voltar para Pesquisa</a>
            </span>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="subscription-container">
    <div class="subscription-header">
        <h1>Minha Assinatura</h1>
    </div>
    
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
                <a href="{{ route('refund.create') }}" class="btn-danger-outline">
                    Solicitar Estorno
                </a>
            </div>
            
            <p class="text-muted mt-3" style="font-size: 0.85rem;">
                No portal, você pode atualizar seu cartão, trocar de plano ou cancelar.
            </p>
        </div>
        
        <div class="subscription-card">
            <h3>Seus Benefícios</h3>
            <ul style="margin-top: 15px;">
                @if($user->hasFeature('no_ads'))
                <li>✓ Navegação sem anúncios</li>
                @endif
                @if($user->hasFeature('exclusive_content'))
                <li>✓ Acesso a conteúdo exclusivo</li>
                @endif
                @if($user->hasFeature('ai_tools'))
                <li>✓ Ferramentas de IA</li>
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
    
    <div class="text-center mt-4">
        <a href="{{ route('searchpage') }}" style="color: #667eea;">← Voltar ao site</a>
    </div>
</div>
@endsection
