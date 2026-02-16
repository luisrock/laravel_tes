<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $label = 'Contato';
        $description = 'Envie sua mensagem para a equipe do Teses & Súmulas.';
        $prefilledEmail = auth()->user()?->email;

        return view('front.contact', compact('label', 'description', 'prefilledEmail'));
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        if ($request->user()) {
            $validatedData['email'] = $request->user()->email;
        }

        Mail::to(config('mail.contact_recipient'))
            ->send(
                (new ContactMessageMail(
                    name: $validatedData['name'],
                    email: $validatedData['email'],
                    contactSubject: $validatedData['subject'],
                    body: $validatedData['message'],
                ))
                    ->replyTo($validatedData['email'], $validatedData['name'])
            );

        return redirect()
            ->route('contact.index')
            ->with('success', 'Mensagem enviada com sucesso. Obrigado pelo contato!');
    }
}
