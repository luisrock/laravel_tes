<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRequestReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected RefundRequest $refundRequest;

    public function __construct(RefundRequest $refundRequest)
    {
        $this->refundRequest = $refundRequest;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Recebemos sua solicitação de estorno')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Recebemos sua solicitação de estorno e ela será analisada pela nossa equipe.')
            ->line('Prazo de resposta: até 5 dias úteis.')
            ->line('Você receberá um email com nossa decisão.')
            ->action('Ver Status da Assinatura', route('subscription.show'))
            ->line('Obrigado pela paciência.');
    }
}
