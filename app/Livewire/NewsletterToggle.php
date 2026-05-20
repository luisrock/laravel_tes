<?php

namespace App\Livewire;

use App\Enums\NewsletterEventSource;
use App\Services\Sendy\NewsletterSubscriptionContext;
use App\Services\Sendy\SendyService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class NewsletterToggle extends Component
{
    public bool $subscribed = false;

    public bool $loading = false;

    public ?string $message = null;

    public ?string $messageType = null;

    public function mount(SendyService $sendy): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $this->subscribed = $sendy->syncUserSubscriptionState($user);
    }

    public function subscribe(SendyService $sendy): void
    {
        if (! $sendy->isEnabled()) {
            $this->setError('Inscrições indisponíveis no momento.');

            return;
        }

        $this->loading = true;
        $this->message = null;

        $user = Auth::user();

        if ($user === null) {
            $this->setError('Faça login para gerir a newsletter.');
            $this->loading = false;

            return;
        }

        $result = $sendy->subscribe(
            $user->email,
            $user->name,
            new NewsletterSubscriptionContext(
                source: NewsletterEventSource::PanelToggle,
                userId: $user->id,
                ip: request()->ip(),
                userAgent: substr((string) request()->userAgent(), 0, 512),
                silent: (bool) config('services.sendy.silent_authenticated', true),
            ),
        );

        $this->subscribed = $result->success || $result->alreadySubscribed;
        $this->message = $this->subscribed
            ? 'Inscrição confirmada!'
            : 'Não foi possível inscrever agora.';
        $this->messageType = $this->subscribed ? 'success' : 'error';
        $this->loading = false;
    }

    public function unsubscribe(SendyService $sendy): void
    {
        if (! $sendy->isEnabled()) {
            $this->setError('Inscrições indisponíveis no momento.');

            return;
        }

        $this->loading = true;
        $this->message = null;

        $user = Auth::user();

        if ($user === null) {
            $this->setError('Faça login para gerir a newsletter.');
            $this->loading = false;

            return;
        }

        $result = $sendy->unsubscribe(
            $user->email,
            new NewsletterSubscriptionContext(
                source: NewsletterEventSource::PanelToggle,
                userId: $user->id,
                ip: request()->ip(),
                userAgent: substr((string) request()->userAgent(), 0, 512),
            ),
        );

        $this->subscribed = ! $result->success;
        $this->message = $result->success
            ? 'Você saiu da lista.'
            : 'Não foi possível sair da lista agora.';
        $this->messageType = $result->success ? 'success' : 'error';
        $this->loading = false;
    }

    public function render(): View
    {
        return view('livewire.newsletter-toggle');
    }

    private function setError(string $message): void
    {
        $this->message = $message;
        $this->messageType = 'error';
    }
}
