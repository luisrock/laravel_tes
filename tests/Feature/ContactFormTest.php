<?php

use App\Mail\ContactMessageMail;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

describe('Contato', function () {
    beforeEach(function () {
        Config::set('honeypot.enabled', false);
        Config::set('mail.contact_recipient', 'maurolopes@tesesesumulas.com.br');
    });

    it('carrega a página de contato', function () {
        $this->get('/contato')
            ->assertSuccessful()
            ->assertSee('Contato');
    });

    it('envia mensagem de contato e mostra feedback de sucesso', function () {
        Mail::fake();

        $this->post('/contato', [
            'name' => 'Visitante Teste',
            'email' => 'visitante@example.com',
            'subject' => 'Dúvida sobre assinatura',
            'message' => 'Olá, gostaria de entender melhor os recursos disponíveis no plano.',
        ])
            ->assertRedirect(route('contact.index'))
            ->assertSessionHas('success');

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasTo('maurolopes@tesesesumulas.com.br')
                && $mail->hasReplyTo('visitante@example.com')
                && $mail->name === 'Visitante Teste'
                && $mail->email === 'visitante@example.com';
        });
    });

    it('prefill e bloqueia campo de e-mail para usuário logado', function () {
        $user = User::factory()->create([
            'email' => 'usuario.logado@example.com',
        ]);

        $this->actingAs($user)
            ->get('/contato')
            ->assertSuccessful()
            ->assertSee('value="usuario.logado@example.com"', false)
            ->assertSee('readonly', false);
    });

    it('força uso do e-mail do usuário logado ao enviar contato', function () {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'real@example.com',
        ]);

        $this->actingAs($user)
            ->post('/contato', [
                'name' => 'Usuário Logado',
                'email' => 'tentativa-manipulacao@example.com',
                'subject' => 'Assunto legítimo',
                'message' => 'Esta mensagem deve sair com o e-mail do usuário autenticado.',
            ])
            ->assertRedirect(route('contact.index'))
            ->assertSessionHas('success');

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasReplyTo('real@example.com')
                && $mail->email === 'real@example.com';
        });
    });

    it('valida campos obrigatórios no envio do formulário', function () {
        $this->post('/contato', [])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    });
});
