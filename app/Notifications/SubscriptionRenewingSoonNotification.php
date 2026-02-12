<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewingSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Carbon $renewsAt,
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
        return (new MailMessage)
            ->subject('Sua assinatura sera renovada em breve')
            ->greeting('Ola, '.$notifiable->name)
            ->line('Sua assinatura do Teses e Sumulas sera renovada automaticamente em '.$this->renewsAt->format('d/m/Y').'.')
            ->line('Se voce deseja cancelar ou alterar seu plano, pode fazer isso a qualquer momento.')
            ->action('Gerenciar Assinatura', route('subscription.portal'))
            ->line('Obrigado por continuar conosco!');
    }
}
