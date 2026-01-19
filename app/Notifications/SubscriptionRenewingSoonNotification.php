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

    protected Carbon $renewsAt;

    public function __construct(Carbon $renewsAt)
    {
        $this->renewsAt = $renewsAt;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Sua assinatura será renovada em breve')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Sua assinatura do Teses e Súmulas será renovada automaticamente em ' . $this->renewsAt->format('d/m/Y') . '.')
            ->line('Se você deseja cancelar ou alterar seu plano, pode fazer isso a qualquer momento.')
            ->action('Gerenciar Assinatura', route('subscription.portal'))
            ->line('Obrigado por continuar conosco!');
    }
}
