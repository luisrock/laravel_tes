@extends('layouts.app')

@section('page-title', 'Pagamento em Processamento')

@section('content')
<div class="tw-max-w-7xl tw-mx-auto tw-py-16 tw-px-4 sm:tw-px-6 lg:tw-px-8">
    <div class="tw-max-w-2xl tw-mx-auto tw-text-center">
        
        {{-- Estado: Processando --}}
        <div id="processing-state" class="{{ $isProcessed ? 'tw-hidden' : '' }}">
            <div class="tw-mx-auto tw-h-20 tw-w-20 tw-border-4 tw-border-slate-200 tw-border-t-brand-600 tw-rounded-full tw-animate-spin tw-mb-8"></div>
            
            <h1 class="tw-text-3xl tw-font-extrabold tw-text-slate-900 tw-mb-4">Processando seu pagamento...</h1>
            <p class="tw-text-lg tw-text-slate-600 tw-mb-8">
                Aguarde enquanto confirmamos seu pagamento com o Stripe.<br>
                Isso pode levar alguns segundos.
            </p>
            
            <div id="timeout-message" class="tw-hidden tw-mt-6 tw-bg-slate-50 tw-rounded-md tw-p-4 tw-border tw-border-slate-200">
                <p class="tw-text-slate-600">
                    Ainda estamos processando seu pagamento.<br>
                    Você pode aguardar mais alguns instantes ou verificar sua assinatura mais tarde.
                </p>
                <div class="tw-mt-4">
                    <a href="{{ route('subscription.show') }}" class="tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">
                        Verificar minha assinatura
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Estado: Sucesso --}}
        <div id="success-state" class="{{ $isProcessed ? '' : 'tw-hidden' }}">
            <div class="tw-mx-auto tw-h-20 tw-w-20 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-8">
                <svg class="tw-h-12 tw-w-12 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="tw-text-3xl tw-font-extrabold tw-text-slate-900 tw-mb-4">Pagamento Confirmado!</h1>
            <p class="tw-text-lg tw-text-slate-600 tw-mb-10">
                Obrigado por assinar o Teses & Súmulas!<br>
                Você agora tem acesso a todos os benefícios do seu plano.
            </p>
            
            <a href="{{ route('subscription.show') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors text-decoration-none">
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
                    document.getElementById('processing-state').classList.add('tw-hidden');
                    document.getElementById('success-state').classList.remove('tw-hidden');
                } else if (attempts < maxAttempts) {
                    setTimeout(checkStatus, 1000);
                } else {
                    // Timeout - manter estado de processamento e orientar usuário
                    document.getElementById('timeout-message').classList.remove('tw-hidden');
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
