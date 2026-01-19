@extends('front.base', ['display_pdf' => false])

@section('page-title', 'Pagamento em Processamento')

@section('styles')
<style>
    .success-container {
        max-width: 600px;
        margin: 60px auto;
        padding: 40px;
        text-align: center;
    }
    
    .processing-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 30px;
        border: 4px solid #e0e0e0;
        border-top-color: #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 30px;
        background: #27ae60;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 40px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .success-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 40px;
    }
    
    .success-title {
        font-size: 1.8rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .success-message {
        color: #666;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .btn-primary {
        display: inline-block;
        padding: 15px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: opacity 0.2s ease;
    }
    .btn-primary:hover {
        opacity: 0.9;
        color: white;
        text-decoration: none;
    }
    
    .hidden {
        display: none;
    }

    .timeout-message {
        margin-top: 20px;
        color: #666;
        font-size: 0.95rem;
        line-height: 1.5;
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
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="success-container">
    <div class="success-card">
        {{-- Estado: Processando --}}
        <div id="processing-state" class="{{ $isProcessed ? 'hidden' : '' }}">
            <div class="processing-icon"></div>
            <h1 class="success-title">Processando seu pagamento...</h1>
            <p class="success-message">
                Aguarde enquanto confirmamos seu pagamento com o Stripe.<br>
                Isso pode levar alguns segundos.
            </p>
            <div id="timeout-message" class="timeout-message hidden">
                Ainda estamos processando seu pagamento.<br>
                Você pode aguardar mais alguns instantes ou verificar sua assinatura mais tarde.
            </div>
        </div>
        
        {{-- Estado: Sucesso --}}
        <div id="success-state" class="{{ $isProcessed ? '' : 'hidden' }}">
            <div class="success-icon">✓</div>
            <h1 class="success-title">Pagamento Confirmado!</h1>
            <p class="success-message">
                Obrigado por assinar o Teses & Súmulas!<br>
                Você agora tem acesso a todos os benefícios do seu plano.
            </p>
            <a href="{{ route('subscription.show') }}" class="btn-primary">
                Ver minha assinatura
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionId = '{{ $sessionId }}';
    const isProcessed = {{ $isProcessed ? 'true' : 'false' }};
    
    if (isProcessed) {
        return; // Já processado, não precisa fazer polling
    }
    
    // Polling para verificar status
    let attempts = 0;
    const maxAttempts = 30; // 30 tentativas = ~30 segundos
    
    function checkStatus() {
        attempts++;
        
        fetch('{{ route("subscription.check-status") }}?session_id=' + sessionId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed') {
                    document.getElementById('processing-state').classList.add('hidden');
                    document.getElementById('success-state').classList.remove('hidden');
                } else if (attempts < maxAttempts) {
                    setTimeout(checkStatus, 1000);
                } else {
                    // Timeout - manter estado de processamento e orientar usuário
                    document.getElementById('timeout-message').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Erro ao verificar status:', error);
                if (attempts < maxAttempts) {
                    setTimeout(checkStatus, 2000);
                }
            });
    }
    
    // Iniciar polling após 1 segundo
    setTimeout(checkStatus, 1000);
});
</script>
@endsection
