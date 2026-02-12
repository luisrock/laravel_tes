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

    public function __construct(
        protected ?Carbon $endsAt = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Sua assinatura foi cancelada')
            ->greeting('Ola, '.$notifiable->name)
            ->line('Confirmamos o cancelamento da sua assinatura.');

        if ($this->endsAt) {
            $message->line('Voce ainda tera acesso ate: '.$this->endsAt->format('d/m/Y'));
        }

        return $message
            ->line('Sentiremos sua falta! Se mudar de ideia, pode reativar a qualquer momento.')
            ->action('Reativar Assinatura', route('subscription.plans'))
            ->line('Obrigado por ter sido assinante do Teses e Sumulas.');
    }
}
