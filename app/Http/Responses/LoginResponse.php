<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $redirect = $request->input('redirect');

        // Verifica se é uma rota relativa válida (sem domínio/host malicioso)
        if ($redirect && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            return redirect()->to($redirect);
        }

        return redirect()->intended(config('fortify.home'));
    }
}
