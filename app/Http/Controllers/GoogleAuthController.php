<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class GoogleAuthController extends Controller
{
    /**
     * Redireciona o usuário para a página de autenticação do Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Recebe o callback do Google e autentica o usuário.
     */
    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::query()->where('google_id', $googleUser->getId())->first()
            ?? User::query()->where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name' => $this->generateUniqueName($googleUser->getName()),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);

            $role = Role::findOrCreate('registered', 'web');
            $user->assignRole($role);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(config('fortify.home', '/minha-conta'));
    }

    /**
     * Gera um nome único, adicionando sufixo numérico se já existir.
     */
    private function generateUniqueName(string $name): string
    {
        if (! User::query()->where('name', $name)->exists()) {
            return $name;
        }

        $counter = 2;
        while (User::query()->where('name', "{$name} {$counter}")->exists()) {
            $counter++;
        }

        return "{$name} {$counter}";
    }
}
