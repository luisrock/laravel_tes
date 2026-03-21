<?php

namespace App\Http\Controllers;

use App\Models\ContentView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class TestToolbarController extends Controller
{
    public function resetViews(Request $request): RedirectResponse
    {
        abort_unless($this->isTestUser($request), 403);

        ContentView::where('user_id', $request->user()->id)->delete();

        return back()->with('test-toolbar-message', 'Contador de views zerado.');
    }

    public function switchRole(Request $request): RedirectResponse
    {
        abort_unless($this->isTestUser($request), 403);

        $role = $request->input('role');
        abort_unless(in_array($role, ['registered', 'subscriber', 'premium'], true), 422);

        $user = $request->user();
        $user->syncRoles([Role::findByName($role, 'web')]);

        return back()->with('test-toolbar-message', "Perfil alterado para: {$role}.");
    }

    private function isTestUser(Request $request): bool
    {
        if (! config('teses.test_toolbar_enabled')) {
            return false;
        }

        $email = config('teses.test_toolbar_email');

        return $email !== null && $email !== '' && $request->user()?->email === $email;
    }
}
