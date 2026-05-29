<?php

namespace App\Livewire;

use App\Ai\Agents\StatsAnalyst;
use App\Services\Newsletter\SiteMetrics;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

/**
 * Chat (admin) para conversar com o modelo de IA sobre as estatísticas do site, mais um botão de
 * avaliação one-click do período selecionado. Resposta síncrona; histórico efêmero (vive na sessão
 * Livewire, não é persistido).
 */
class StatsAiChat extends Component
{
    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public string $input = '';

    public string $period = '30';

    public ?string $error = null;

    public function mount(string $period = '30'): void
    {
        $this->period = array_key_exists($period, SiteMetrics::PERIOD_OPTIONS) ? $period : '30';
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

        $history = $this->messages;

        // A resposta é síncrona e o tool-calling pode ter vários passos; damos folga ao limite de
        // execução do PHP, mantendo-o acima do timeout HTTP do agente para que um estouro vire erro tratável.
        if (function_exists('set_time_limit')) {
            @set_time_limit((int) config('services.openrouter.request_timeout', 120) + 30);
        }

        try {
            $response = (new StatsAnalyst($history))->prompt($prompt);

            $this->messages[] = ['role' => 'user', 'content' => $prompt];
            $this->messages[] = ['role' => 'assistant', 'content' => (string) $response];
            $this->input = '';
        } catch (Throwable $e) {
            report($e);
            $this->error = 'Não foi possível obter a resposta do modelo agora. Tente novamente em instantes.';
        }
    }

    public function clearConversation(): void
    {
        $this->messages = [];
        $this->error = null;
        $this->input = '';
    }

    public function render(): View
    {
        return view('livewire.stats-ai-chat', [
            'periodOptions' => SiteMetrics::PERIOD_OPTIONS,
            'isConfigured' => StatsAnalyst::isConfigured(),
        ]);
    }
}
