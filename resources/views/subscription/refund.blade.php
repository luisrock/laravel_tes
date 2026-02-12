@extends('layouts.user-panel')

@section('panel-title', 'Solicitar Estorno')

@section('panel-styles')
<style>
    .refund-container { margin: 0 auto; max-width: 100%; }
    .refund-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        padding: 24px;
    }
    .refund-info {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 24px;
    }
    .refund-info h4 {
        font-size: 0.9375rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #212529;
    }
    .refund-info p {
        color: #495057;
        margin: 0;
        font-size: 0.9375rem;
        line-height: 1.5;
    }
    .refund-card .form-group { margin-bottom: 20px; }
    .refund-card .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #212529;
        font-size: 0.9375rem;
    }
    .refund-card .form-group textarea {
        width: 100%;
        min-height: 140px;
        padding: 12px 14px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 1rem;
        resize: vertical;
        transition: border-color 0.15s ease;
    }
    .refund-card .form-group textarea:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }
    .refund-card .form-group small {
        color: #6c757d;
        display: block;
        margin-top: 6px;
        font-size: 0.8125rem;
    }
    .error-message { color: #dc3545; font-size: 0.875rem; margin-top: 6px; }
    .refund-card .btn-group { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 24px; }
    .refund-card .btn-primary {
        padding: 12px 24px;
        background: #0d6efd;
        color: #fff;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9375rem;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .refund-card .btn-primary:hover { background: #0b5ed7; }
    .refund-card .btn-secondary {
        padding: 12px 24px;
        background: #fff;
        color: #212529;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9375rem;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .refund-card .btn-secondary:hover {
        background: #f8f9fa;
        color: #212529;
        text-decoration: none;
    }
    .refund-card .alert { padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; }
    .alert-warning { background: #fff3cd; color: #664d03; }
</style>
@endsection

@section('panel-content')
<div class="refund-container">
    
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
        
        <p class="text-muted mt-4" style="font-size: 0.875rem; color: #6c757d;">
            Sua solicitação será analisada pela nossa equipe em até 48 horas úteis. Você receberá uma resposta por e-mail.
        </p>
    </div>
</div>
@endsection
