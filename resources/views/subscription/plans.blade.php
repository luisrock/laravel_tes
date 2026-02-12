@extends('front.base', ['display_pdf' => false])

@section('page-title', 'Planos de Assinatura')

@section('styles')
<style>
    .plans-page {
        background: #f8f9fa;
        min-height: 70vh;
        padding: 40px 0 60px;
    }
    
    .plans-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .plans-header {
        text-align: center;
        margin-bottom: 40px;
    }
    .plans-header h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    .plans-header p {
        color: #666;
        font-size: 1rem;
        margin: 0;
    }
    
    .alert-notice {
        background: #fff;
        border: 1px solid #dee2e6;
        border-left: 3px solid #0d6efd;
        border-radius: 4px;
        padding: 15px 20px;
        margin-bottom: 30px;
        font-size: 0.95rem;
        color: #495057;
    }
    .alert-notice a {
        color: #0d6efd;
        text-decoration: none;
    }
    .alert-notice a:hover {
        text-decoration: underline;
    }
    
    .plans-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 40px;
    }
    @media (min-width: 768px) {
        .plans-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .plan-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 30px;
    }
    .plan-card.featured {
        border-color: #0d6efd;
        box-shadow: 0 0 0 1px #0d6efd;
    }
    
    .plan-badge {
        display: inline-block;
        background: #0d6efd;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 3px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .plan-name {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    .plan-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    .price-selector {
        margin-bottom: 20px;
    }
    .price-option {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: border-color 0.15s ease;
    }
    .price-option:hover {
        border-color: #adb5bd;
    }
    .price-option.selected {
        border-color: #0d6efd;
        background: #f8f9ff;
    }
    .price-option input[type="radio"] {
        margin-right: 12px;
        accent-color: #0d6efd;
    }
    .price-details {
        flex: 1;
    }
    .price-amount {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }
    .price-period {
        color: #666;
        font-size: 0.85rem;
    }
    .price-savings {
        background: #d1e7dd;
        color: #0f5132;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 3px;
        margin-left: 10px;
    }
    
    .plan-features {
        list-style: none;
        padding: 0;
        margin: 0 0 25px 0;
    }
    .plan-features li {
        padding: 8px 0;
        color: #495057;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }
    .plan-features li::before {
        content: '✓';
        color: #198754;
        font-weight: bold;
        margin-right: 10px;
        font-size: 0.85rem;
    }
    
    .btn-subscribe {
        display: block;
        width: 100%;
        padding: 12px 20px;
        font-size: 0.95rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .btn-subscribe-primary {
        background: #0d6efd;
        color: #fff;
    }
    .btn-subscribe-primary:hover {
        background: #0b5ed7;
        color: #fff;
        text-decoration: none;
    }
    .btn-subscribe-outline {
        background: transparent;
        color: #0d6efd;
        border: 1px solid #0d6efd;
    }
    .btn-subscribe-outline:hover {
        background: #0d6efd;
        color: #fff;
        text-decoration: none;
    }
    .btn-subscribe:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .plans-footer {
        text-align: center;
        color: #6c757d;
        font-size: 0.85rem;
    }
    .plans-footer a {
        color: #0d6efd;
        text-decoration: none;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c2c7;
        border-radius: 4px;
        padding: 12px 15px;
        color: #842029;
        margin-bottom: 20px;
    }
    .alert-info {
        background: #cff4fc;
        border: 1px solid #b6effb;
        border-radius: 4px;
        padding: 12px 15px;
        color: #055160;
        margin-bottom: 20px;
    }
    .alert-info a {
        color: #055160;
        font-weight: 600;
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
        <p>
            Escolha um plano e navegue sem anúncios, com acesso a conteúdo exclusivo.
        </p>
    </div>
</div>
<!-- END Hero -->

<div class="plans-page">
    <div class="plans-container">
        <div class="plans-header">
            <h1>Escolha seu plano</h1>
            <p>Navegue sem anúncios e acesse conteúdo exclusivo</p>
        </div>
        
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert-info">{{ session('info') }}</div>
        @endif
        
        @guest
            <div class="alert-notice">
                Para assinar, você precisa ter uma conta. <a href="{{ route('login') }}">Faça login</a>.
            </div>
        @endguest
        
        @auth
            @if(auth()->user()->isSubscriber())
                <div class="alert-info">
                    Você já é assinante! <a href="{{ route('subscription.show') }}">Gerencie sua assinatura</a>
                </div>
            @endif
        @endauth
        
        <div class="plans-grid">
            @foreach($plans as $tier => $plan)
            <div class="plan-card {{ $tier === 'premium' ? 'featured' : '' }}">
                @if($tier === 'premium')
                    <span class="plan-badge">Recomendado</span>
                @endif
                
                <h2 class="plan-name">{{ $plan['name'] }}</h2>
                <p class="plan-description">{{ $plan['description'] ?? 'Acesse todos os benefícios do plano ' . $plan['name'] }}</p>
                
                <form action="{{ route('subscription.checkout') }}" method="POST">
                    @csrf
                    
                    <div class="price-selector">
                        @if(isset($plan['prices']['monthly']))
                        <label class="price-option selected">
                            <input type="radio" name="priceId" value="{{ $plan['prices']['monthly']['id'] }}" checked>
                            <div class="price-details">
                                <span class="price-amount">R$ {{ number_format($plan['prices']['monthly']['amount'], 2, ',', '.') }}</span>
                                <span class="price-period">/ mês</span>
                            </div>
                        </label>
                        @endif
                        
                        @if(isset($plan['prices']['yearly']))
                        <label class="price-option">
                            <input type="radio" name="priceId" value="{{ $plan['prices']['yearly']['id'] }}">
                            <div class="price-details">
                                <span class="price-amount">R$ {{ number_format($plan['prices']['yearly']['amount'], 2, ',', '.') }}</span>
                                <span class="price-period">/ ano</span>
                                @php
                                    $monthlyTotal = isset($plan['prices']['monthly']) ? $plan['prices']['monthly']['amount'] * 12 : 0;
                                    $savings = $monthlyTotal - $plan['prices']['yearly']['amount'];
                                    $savingsPercent = $monthlyTotal > 0 ? round(($savings / $monthlyTotal) * 100) : 0;
                                @endphp
                                @if($savingsPercent > 0)
                                <span class="price-savings">-{{ $savingsPercent }}%</span>
                                @endif
                            </div>
                        </label>
                        @endif
                    </div>
                    
                    <ul class="plan-features">
                        <li>Navegação sem anúncios</li>
                        <li>Acesso a conteúdo exclusivo</li>
                        @if($tier === 'premium')
                        <li>Ferramentas de IA (em breve)</li>
                        <li>Suporte prioritário</li>
                        @endif
                    </ul>
                    
                    @auth
                        @if(!auth()->user()->isSubscriber())
                        <button type="submit" class="btn-subscribe {{ $tier === 'premium' ? 'btn-subscribe-primary' : 'btn-subscribe-outline' }}">
                            Assinar {{ $plan['name'] }}
                        </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-subscribe {{ $tier === 'premium' ? 'btn-subscribe-primary' : 'btn-subscribe-outline' }}">
                            Faça login para assinar
                        </a>
                    @endauth
                </form>
            </div>
            @endforeach
        </div>
        
        <div class="plans-footer">
            <p>
                Pagamento seguro via Stripe. Cancele quando quiser.<br>
                Dúvidas? <a href="mailto:contato@tesesesumulas.com.br">contato@tesesesumulas.com.br</a>
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.price-option').forEach(function(option) {
        option.addEventListener('click', function() {
            this.closest('.price-selector').querySelectorAll('.price-option').forEach(function(sibling) {
                sibling.classList.remove('selected');
            });
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
});
</script>
@endsection
