<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class UserPanelController extends Controller
{
    /**
     * Dashboard do painel do usuário (visão geral).
     */
    public function dashboard(): View
    {
        return view('user-panel.dashboard');
    }

    /**
     * Página de perfil (dados, senha, 2FA).
     */
    public function profile(): View
    {
        return view('user-panel.profile');
    }
}
