<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSubscriberNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao Teses e Súmulas!')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Sua assinatura foi ativada com sucesso.')
            ->line('Agora você tem acesso a:')
            ->line('✓ Navegação sem anúncios')
            ->line('✓ Conteúdo exclusivo')
            ->action('Explorar Conteúdo', url('/'))
            ->line('Obrigado por assinar o Teses e Súmulas!');
    }
}
