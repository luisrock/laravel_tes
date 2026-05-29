<div>
    <x-filament::section icon="heroicon-o-sparkles" icon-color="primary">
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

            @if (filled($messages))
                <x-filament::button
                    wire:click="clearConversation"
                    color="gray"
                    icon="heroicon-o-trash"
                >
                    Limpar conversa
                </x-filament::button>
            @endif
        </div>

        {{-- Campo de pergunta --}}
        <form wire:submit="send" style="display:flex;align-items:flex-end;gap:0.5rem;margin-top:0.75rem;">
            <x-filament::input.wrapper class="fi-fo-textarea" style="flex:1 1 auto;">
                <textarea
                    wire:model="input"
                    rows="4"
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

        @if ($error)
            <div style="margin-top:1rem;border-radius:0.5rem;padding:0.75rem 1rem;background-color:#fef2f2;color:#b91c1c;font-size:0.875rem;">
                {{ $error }}
            </div>
        @endif

        {{-- Conversa: respostas abaixo dos controles --}}
        <div style="display:flex;flex-direction:column;gap:0.75rem;max-height:28rem;overflow-y:auto;margin-top:1.25rem;">
            {{-- Indicador de carregamento (oculto até haver requisição em curso) --}}
            <div wire:loading.flex wire:target="send, evaluateOnScreen" style="justify-content:flex-start;align-items:center;gap:0.5rem;font-size:0.875rem;color:#6b7280;">
                <x-filament::loading-indicator style="width:1.25rem;height:1.25rem;" />
                A analisar as estatísticas…
            </div>

            @forelse (array_reverse($messages) as $message)
                @if ($message['role'] === 'user')
                    <div style="display:flex;justify-content:flex-end;">
                        <div style="max-width:85%;border-radius:1rem;padding:0.5rem 1rem;font-size:0.875rem;color:#fff;background-color:#912F56;">
                            {{ $message['content'] }}
                        </div>
                    </div>
                @else
                    <div style="display:flex;justify-content:flex-start;">
                        <div class="fi-prose" style="max-width:85%;border-radius:1rem;padding:0.5rem 1rem;font-size:0.875rem;background-color:rgba(120,120,120,0.12);">
                            {!! \Illuminate\Support\Str::markdown($message['content'], ['html_input' => 'strip']) !!}
                        </div>
                    </div>
                @endif
            @empty
                <p style="font-size:0.875rem;color:#6b7280;">
                    Pergunte sobre as métricas do site ou clique em <strong>Avaliar estatísticas</strong> para uma análise automática.
                </p>
            @endforelse
        </div>
    </x-filament::section>
</div>
