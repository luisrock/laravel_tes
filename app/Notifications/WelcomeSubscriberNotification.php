<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSubscriberNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao Teses e Sumulas!')
            ->greeting('Ola, '.$notifiable->name.'!')
            ->line('Sua assinatura foi ativada com sucesso.')
            ->line('Agora voce tem acesso a:')
            ->line('✓ Navegacao sem anuncios')
            ->line('✓ Conteudo exclusivo')
            ->action('Explorar Conteudo', url('/'))
            ->line('Obrigado por assinar o Teses e Sumulas!');
    }
}
