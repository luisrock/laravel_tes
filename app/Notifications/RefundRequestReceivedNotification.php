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

    public function __construct(
        protected RefundRequest $refundRequest,
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
            ->subject('Recebemos sua solicitacao de estorno')
            ->greeting('Ola, '.$notifiable->name)
            ->line('Recebemos sua solicitacao de estorno e ela sera analisada pela nossa equipe.')
            ->line('Prazo de resposta: ate 5 dias uteis.')
            ->line('Voce recebera um email com nossa decisao.')
            ->action('Ver Status da Assinatura', route('subscription.show'))
            ->line('Obrigado pela paciencia.');
    }
}
