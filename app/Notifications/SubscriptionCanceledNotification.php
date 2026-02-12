<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?Carbon $endsAt;

    public function __construct(?Carbon $endsAt = null)
    {
        $this->endsAt = $endsAt;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Sua assinatura foi cancelada')
            ->greeting('Olá, '.$notifiable->name)
            ->line('Confirmamos o cancelamento da sua assinatura.');

        if ($this->endsAt) {
            $message->line('Você ainda terá acesso até: '.$this->endsAt->format('d/m/Y'));
        }

        return $message
            ->line('Sentiremos sua falta! Se mudar de ideia, pode reativar a qualquer momento.')
            ->action('Reativar Assinatura', route('subscription.plans'))
            ->line('Obrigado por ter sido assinante do Teses e Súmulas.');
    }
}
