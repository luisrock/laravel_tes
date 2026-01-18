@extends('front.base', ['display_pdf' => false])

@section('page-title', 'Solicitar Estorno')

@section('styles')
<style>
    .refund-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .refund-header {
        margin-bottom: 30px;
    }
    .refund-header h1 {
        font-size: 2rem;
        color: #333;
    }
    
    .refund-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 30px;
    }
    
    .refund-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
    }
    .refund-info h4 {
        margin-bottom: 10px;
        color: #333;
    }
    .refund-info p {
        color: #666;
        margin: 0;
        font-size: 0.95rem;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    .form-group textarea {
        width: 100%;
        min-height: 150px;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        resize: vertical;
        transition: border-color 0.2s ease;
    }
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    .form-group small {
        color: #999;
        display: block;
        margin-top: 5px;
    }
    
    .error-message {
        color: #dc3545;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 25px;
    }
    
    .btn-primary {
        padding: 15px 30px;
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
    }
    
    .btn-secondary {
        padding: 15px 30px;
        background: #f0f0f0;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .btn-secondary:hover {
        background: #e0e0e0;
        color: #333;
        text-decoration: none;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-warning {
        background: #fff3cd;
        color: #856404;
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
                <a href="{{ route('subscription.show') }}" class="badge badge-secondary">Voltar para Assinatura</a>
            </span>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="refund-container">
    <div class="refund-header">
        <h1>Solicitar Estorno</h1>
    </div>
    
    <div class="alert alert-warning">
        <strong>Importante:</strong> Antes de solicitar estorno, considere cancelar sua assinatura. Você manterá acesso até o fim do período pago. O estorno é irreversível e encerrará seu acesso imediatamente.
    </div>
    
    <div class="refund-card">
        <div class="refund-info">
            <h4>Sua Assinatura</h4>
            <p>
                <strong>Plano:</strong> {{ $subscription->stripe_id ?? 'N/A' }}<br>
                <strong>Desde:</strong> {{ $subscription->created_at->format('d/m/Y') }}
            </p>
        </div>
        
        <form action="{{ route('refund.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="reason">Motivo da solicitação</label>
                <textarea 
                    name="reason" 
                    id="reason" 
                    placeholder="Por favor, descreva o motivo da sua solicitação de estorno. Isso nos ajuda a melhorar nossos serviços."
                    required
                    minlength="20"
                    maxlength="1000"
                >{{ old('reason') }}</textarea>
                <small>Mínimo de 20 caracteres</small>
                @error('reason')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn-primary">
                    Enviar Solicitação
                </button>
                <a href="{{ route('subscription.show') }}" class="btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
        
        <p class="text-muted mt-4" style="font-size: 0.85rem;">
            Sua solicitação será analisada pela nossa equipe em até 48 horas úteis. Você receberá uma resposta por e-mail.
        </p>
    </div>
</div>
@endsection
