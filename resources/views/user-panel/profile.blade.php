@extends('layouts.user-panel')

@section('panel-title', 'Perfil')

@section('panel-styles')
<style>
    .profile-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        padding: 24px;
        margin-bottom: 24px;
    }
    .profile-card h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 20px 0;
        color: #212529;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .profile-card .form-group { margin-bottom: 20px; }
    .profile-card .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #212529;
        font-size: 0.9375rem;
    }
    .profile-card .form-group input {
        width: 100%;
        max-width: 400px;
        padding: 12px 14px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.15s ease;
    }
    .profile-card .form-group input:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }
    .btn-save {
        padding: 12px 24px;
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .btn-save:hover { background: #0b5ed7; }
    .btn-danger-outline {
        padding: 12px 24px;
        background: transparent;
        color: #dc3545;
        border: 1px solid #dc3545;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .btn-danger-outline:hover {
        background: #dc3545;
        color: #fff;
    }
    .profile-card .alert { padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; }
    .profile-card .alert-success { background: #d4edda; color: #155724; }
    .profile-card p { color: #495057; font-size: 0.9375rem; line-height: 1.5; }
    .recovery-codes-link {
        display: inline-block;
        margin-top: 12px;
        color: #0d6efd;
        font-size: 0.9375rem;
        font-weight: 500;
        text-decoration: none;
    }
    .recovery-codes-link:hover { text-decoration: underline; color: #0a58ca; }
</style>
@endsection

@section('panel-content')
@if(session('status') === 'profile-information-updated')
    <div class="alert alert-success">Perfil atualizado com sucesso.</div>
@endif
@if(session('status') === 'password-updated')
    <div class="alert alert-success">Senha atualizada com sucesso.</div>
@endif

<div class="profile-card" id="profile-info">
    <h3>Informações do perfil</h3>
    <form method="POST" action="{{ url('/user/profile-information') }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nome</label>
            <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required autofocus autocomplete="name">
        </div>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="username">
            @error('email')
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="btn-save">Salvar</button>
    </form>
</div>

<div class="profile-card">
    <h3>Atualizar senha</h3>
    <form method="POST" action="{{ url('/user/password') }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="current_password">Senha atual</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label for="password">Nova senha</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirmar nova senha</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn-save">Atualizar senha</button>
    </form>
</div>

<div class="profile-card" id="2fa">
    <h3>Autenticação em dois fatores</h3>
    @if(auth()->user()->two_factor_confirmed_at)
        <p>2FA está ativo. Para desativar, confirme sua senha e clique em Desativar.</p>
        <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="d-inline" onsubmit="return confirm('Tem certeza que deseja desativar a autenticação em dois fatores?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger-outline">Desativar 2FA</button>
        </form>
        <a href="{{ url('/user/two-factor-recovery-codes') }}" class="recovery-codes-link">Ver códigos de recuperação</a>
    @else
        <p>Adicione segurança adicional à sua conta usando autenticação em dois fatores.</p>
        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
            @csrf
            <button type="submit" class="btn-save">Ativar 2FA</button>
        </form>
    @endif
</div>
@endsection
