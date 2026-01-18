@extends('front.base', ['display_pdf' => false])

@section('page-title', 'Checkout Cancelado')

@section('styles')
<style>
    .cancel-container {
        max-width: 600px;
        margin: 60px auto;
        padding: 40px;
        text-align: center;
    }
    
    .cancel-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 30px;
        background: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        font-size: 40px;
    }
    
    .cancel-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 40px;
    }
    
    .cancel-title {
        font-size: 1.8rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .cancel-message {
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
        margin: 5px;
        transition: opacity 0.2s ease;
    }
    .btn-primary:hover {
        opacity: 0.9;
        color: white;
        text-decoration: none;
    }
    
    .btn-secondary {
        display: inline-block;
        padding: 15px 30px;
        background: #f0f0f0;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        margin: 5px;
        transition: background 0.2s ease;
    }
    .btn-secondary:hover {
        background: #e0e0e0;
        color: #333;
        text-decoration: none;
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

<div class="cancel-container">
    <div class="cancel-card">
        <div class="cancel-icon">✕</div>
        <h1 class="cancel-title">Checkout Cancelado</h1>
        <p class="cancel-message">
            Você cancelou o processo de checkout.<br>
            Nenhuma cobrança foi realizada.
        </p>
        <div>
            <a href="{{ route('subscription.plans') }}" class="btn-primary">
                Ver planos novamente
            </a>
            <a href="{{ route('searchpage') }}" class="btn-secondary">
                Voltar ao site
            </a>
        </div>
    </div>
</div>
@endsection
