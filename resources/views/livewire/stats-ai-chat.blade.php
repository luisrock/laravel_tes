<div>
    <x-filament::section
        icon="heroicon-o-sparkles"
        icon-color="primary"
        collapsible
        persist-collapsed
        collapse-id="stats-ai-chat"
    >
        <x-slot name="heading">Assistente de estatísticas (IA)</x-slot>

        <x-slot name="description">
            Converse com o modelo sobre as métricas do site ou peça uma avaliação automática do período.
        </x-slot>

        @unless ($isConfigured)
            <div style="margin-bottom:1rem;border-radius:0.5rem;padding:0.75rem 1rem;background-color:#fffbeb;color:#92400e;font-size:0.875rem;">
                Nenhum modelo de IA está configurado.
                <a href="/admin/painel/configuracoes-ia" style="font-weight:600;text-decoration:underline;">Configurar agora</a>.
            </div>
        @endunless

        {{-- Controles: período + avaliação one-click + limpar --}}
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:0.5rem;">
            <x-filament::input.wrapper style="width:auto;">
                <x-filament::input.select wire:model="period">
                    @foreach ($periodOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::button
                wire:click="evaluateOnScreen"
                wire:loading.attr="disabled"
                wire:target="send, evaluateOnScreen"
                icon="heroicon-o-chart-bar"
                :disabled="! $isConfigured"
            >
                Avaliar estatísticas
            </x-filament::button>

            @if (filled($messages) || filled($conversations))
                <x-filament::button
                    wire:click="newConversation"
                    color="gray"
                    icon="heroicon-o-plus"
                >
                    Nova conversa
                </x-filament::button>
            @endif
        </div>

        {{-- Conversas anteriores do admin: selecionar retoma a conversa (título + data/hora). --}}
        @if (filled($conversations))
            <x-filament::input.wrapper style="margin-top:0.75rem;max-width:28rem;">
                <x-filament::input.select wire:change="loadConversation($event.target.value)">
                    <option value="" @selected($conversationId === null)>— Conversas anteriores —</option>
                    @foreach ($conversations as $conversation)
                        <option value="{{ $conversation['id'] }}" @selected($conversation['id'] === $conversationId)>
                            {{ $conversation['label'] }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        @endif

        {{-- Conversa: ordem cronológica, coluna única alinhada à esquerda (largura cheia). --}}
        <div style="display:flex;flex-direction:column;gap:1rem;margin-top:1.25rem;">
            @forelse ($messages as $message)
                <div @style([
                    'border-top:1px solid rgba(120,120,120,0.15);padding-top:1rem;' => ! $loop->first,
                ])>
                    @if ($message['role'] === 'user')
                        <div style="border-left:3px solid #912F56;background-color:rgba(145,47,86,0.06);border-radius:0 0.5rem 0.5rem 0;padding:0.5rem 0.875rem;">
                            <div style="font-size:0.75rem;font-weight:600;color:#912F56;margin-bottom:0.125rem;">Você</div>
                            <div style="font-size:0.875rem;white-space:pre-wrap;color:#374151;">{{ $message['content'] }}</div>
                        </div>
                    @else
                        <div style="font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:0.25rem;">Assistente</div>
                        <div class="fi-prose" style="font-size:0.875rem;">
                            {!! \Illuminate\Support\Str::markdown($message['content'], ['html_input' => 'strip']) !!}
                        </div>
                    @endif
                </div>
            @empty
                <p style="font-size:0.875rem;color:#6b7280;">
                    Pergunte sobre as métricas do site ou clique em <strong>Avaliar estatísticas</strong> para uma análise automática.
                </p>
            @endforelse

            {{-- Streaming ao vivo: deltas chegam token a token (texto cru) numa bolha cinza enquanto a
                 requisição corre; ao concluir, o re-render limpa este alvo e a resposta final renderizada
                 (markdown, sem fundo) entra em $messages acima. A bolha fica oculta enquanto vazia. --}}
            <div wire:loading.flex wire:target="send, evaluateOnScreen" style="flex-direction:column;gap:0.5rem;">
                <div wire:stream="ai-answer" class="stats-ai-stream-bubble" style="font-size:0.875rem;white-space:pre-wrap;color:#374151;background-color:rgba(120,120,120,0.12);border-radius:0.75rem;padding:0.5rem 0.875rem;"></div>
                <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:#6b7280;">
                    <x-filament::loading-indicator style="width:1.25rem;height:1.25rem;" />
                    Pensando…
                </div>
            </div>

            <style>
                /* A bolha cinza de streaming só aparece quando o primeiro token chega. */
                .stats-ai-stream-bubble:empty { display: none; }
            </style>
        </div>

        @if ($error)
            <div style="margin-top:1rem;border-radius:0.5rem;padding:0.75rem 1rem;background-color:#fef2f2;color:#b91c1c;font-size:0.875rem;">
                {{ $error }}
            </div>
        @endif

        {{-- Campo de pergunta no rodapé, como num chat --}}
        <form wire:submit="send" style="display:flex;align-items:flex-end;gap:0.5rem;margin-top:1.25rem;">
            <x-filament::input.wrapper class="fi-fo-textarea" style="flex:1 1 auto;">
                <textarea
                    wire:model="input"
                    rows="3"
                    @unless ($isConfigured) disabled @endunless
                    placeholder="Pergunte sobre as estatísticas…"
                ></textarea>
            </x-filament::input.wrapper>

            <x-filament::button
                type="submit"
                color="primary"
                wire:loading.attr="disabled"
                wire:target="send, evaluateOnScreen"
                :disabled="! $isConfigured"
            >
                Enviar
            </x-filament::button>
        </form>
    </x-filament::section>
</div>
