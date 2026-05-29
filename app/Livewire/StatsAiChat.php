<?php

namespace App\Livewire;

use App\Ai\Agents\StatsAnalyst;
use App\Services\Newsletter\SiteMetrics;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Laravel\Ai\Streaming\Events\Error as StreamErrorEvent;
use Laravel\Ai\Streaming\Events\TextDelta;
use Livewire\Component;
use Throwable;

/**
 * Chat (admin) para conversar com o modelo de IA sobre as estatísticas do site, mais um botão de
 * avaliação one-click do período selecionado. A resposta é transmitida token a token (streaming via
 * `$this->stream()`) e a conversa é persistida por usuário (trait RemembersConversations do SDK):
 * ao abrir, retoma a última conversa do admin; é possível trocar de conversa ou iniciar uma nova.
 */
class StatsAiChat extends Component
{
    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    /** @var array<int, array{id: string, title: string}> */
    public array $conversations = [];

    public ?string $conversationId = null;

    public string $input = '';

    public string $period = '30';

    public ?string $error = null;

    public function mount(string $period = '30'): void
    {
        $this->period = array_key_exists($period, SiteMetrics::PERIOD_OPTIONS) ? $period : '30';

        if (($user = auth()->user()) === null) {
            return;
        }

        $this->conversationId = resolve(ConversationStore::class)->latestConversationId($user->id);
        $this->refreshConversationState();
    }

    public function send(): void
    {
        $this->submitPrompt(trim($this->input));
    }

    public function evaluateOnScreen(): void
    {
        $label = SiteMetrics::periodLabel($this->period);

        $this->submitPrompt(sprintf(
            'Faça uma avaliação das estatísticas do período "%s". Consulte os números, aponte as '
            .'principais tendências, possíveis causas e recomendações práticas para melhorar registos '
            .'e inscrições na newsletter.',
            $label,
        ));
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
        $this->error = null;
        $this->input = '';
    }

    public function loadConversation(string $conversationId): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $belongsToUser = Conversation::query()
            ->whereKey($conversationId)
            ->where('user_id', $user->id)
            ->exists();

        if (! $belongsToUser) {
            return;
        }

        $this->conversationId = $conversationId;
        $this->error = null;
        $this->loadMessages();
    }

    private function submitPrompt(string $prompt): void
    {
        $this->error = null;

        if ($prompt === '') {
            return;
        }

        if (! StatsAnalyst::isConfigured()) {
            $this->error = 'Nenhum modelo de IA está configurado. Defina-o em Configurações de IA.';

            return;
        }

        if (($user = auth()->user()) === null) {
            $this->error = 'É necessário estar autenticado para usar o assistente.';

            return;
        }

        // O streaming melhora a percepção de velocidade, mas a request PHP segue aberta até o modelo
        // terminar (e o tool-calling pode ter vários passos); damos folga ao limite de execução do PHP,
        // mantendo-o acima do timeout HTTP do agente para que um estouro vire erro tratável.
        if (function_exists('set_time_limit')) {
            @set_time_limit((int) config('services.openrouter.request_timeout', 120) + 30);
        }

        try {
            $agent = new StatsAnalyst;

            $this->conversationId !== null
                ? $agent->continue($this->conversationId, $user)
                : $agent->forUser($user);

            foreach ($agent->stream($prompt) as $event) {
                if ($event instanceof StreamErrorEvent) {
                    throw new \RuntimeException($event->message);
                }

                if ($event instanceof TextDelta) {
                    $this->stream(to: 'ai-answer', content: $event->delta, replace: false);
                }
            }

            // A persistência (middleware RememberConversation) já gravou as mensagens e, se necessário,
            // criou a conversa; recarregamos o estado a partir do banco.
            $this->conversationId = $agent->currentConversation();
            $this->input = '';
            $this->refreshConversationState();
        } catch (Throwable $e) {
            report($e);
            $this->error = 'Não foi possível obter a resposta do modelo agora. Tente novamente em instantes.';
        }
    }

    private function refreshConversationState(): void
    {
        $this->loadMessages();
        $this->loadConversations();
    }

    private function loadMessages(): void
    {
        if ($this->conversationId === null) {
            $this->messages = [];

            return;
        }

        $this->messages = ConversationMessage::query()
            ->where('conversation_id', $this->conversationId)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (ConversationMessage $message): bool => filled($message->content))
            ->map(fn (ConversationMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();
    }

    private function loadConversations(): void
    {
        if (($user = auth()->user()) === null) {
            $this->conversations = [];

            return;
        }

        $this->conversations = Conversation::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get(['id', 'title', 'created_at'])
            ->map(fn (Conversation $conversation): array => [
                'id' => $conversation->id,
                'label' => sprintf(
                    '%s — %s',
                    Str::limit($conversation->title, 40),
                    $conversation->created_at?->format('d/m/Y, H:i:s') ?? '',
                ),
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.stats-ai-chat', [
            'periodOptions' => SiteMetrics::PERIOD_OPTIONS,
            'isConfigured' => StatsAnalyst::isConfigured(),
        ]);
    }
}
