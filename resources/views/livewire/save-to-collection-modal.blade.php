<div
    x-data="{ closeTimer: null, showItemLimitCta: false }"
    x-on:open-save-modal.window="showItemLimitCta = false; $wire.open($event.detail.type, $event.detail.tribunal, $event.detail.contentId)"
    x-on:item-toggled.window="clearTimeout(closeTimer); closeTimer = setTimeout(() => $wire.close(), 2000)"
>
    @if ($isOpen)
        {{-- Backdrop --}}
        <div
            class="tw-fixed tw-inset-0 tw-bg-black/50 tw-z-40"
            wire:click="close"
            x-on:keydown.escape.window="$wire.close()"
        ></div>

        {{-- Modal --}}
        <div
            class="tw-fixed tw-inset-0 tw-z-50 tw-flex tw-items-center tw-justify-center tw-p-4"
            role="dialog"
            aria-modal="true"
        >
            <div class="tw-bg-white tw-rounded-xl tw-shadow-2xl tw-w-full tw-max-w-sm tw-flex tw-flex-col tw-max-h-[80vh]">

                {{-- Header --}}
                <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-slate-100">
                    <h3 class="tw-text-sm tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Salvar em coleção</h3>
                    <button wire:click="close" class="tw-text-slate-400 hover:tw-text-slate-600 tw-transition-colors" aria-label="Fechar">
                        <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Lista de coleções --}}
                <div class="tw-overflow-y-auto tw-flex-1">
                    @if (empty($collections))
                        <p class="tw-px-5 tw-py-8 tw-text-sm tw-text-slate-500 tw-text-center">
                            Você ainda não tem coleções.<br>
                            <span class="tw-text-xs">Crie uma abaixo para começar.</span>
                        </p>
                    @else
                        <div class="tw-py-2">
                            @foreach ($collections as $col)
                                <button
                                    @if ($col['is_full'])
                                        @click="showItemLimitCta = true"
                                    @else
                                        wire:click="toggle({{ $col['id'] }})"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="tw-opacity-50"
                                        wire:target="toggle({{ $col['id'] }})"
                                    @endif
                                    class="tw-w-full tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-3 hover:tw-bg-slate-50 tw-transition-colors tw-text-left"
                                >
                                    <span class="tw-shrink-0 tw-w-5 tw-h-5 tw-rounded tw-border-2 tw-flex tw-items-center tw-justify-center tw-transition-colors {{ $col['has_item'] ? 'tw-bg-brand-600 tw-border-brand-600' : 'tw-border-slate-300' }}">
                                        @if ($col['has_item'])
                                            <svg class="tw-w-3 tw-h-3 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="tw-flex-1 tw-text-sm tw-text-slate-700 tw-truncate">{{ $col['title'] }}</span>
                                    @if ($col['id'] === $justToggledId)
                                        {{-- Checkmark verde após toggle --}}
                                        <svg class="tw-w-4 tw-h-4 tw-text-green-500 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Footer: Nova coleção --}}
                <div class="tw-border-t tw-border-slate-100 tw-px-5 tw-py-4">

                    {{-- CTA: limite de itens atingido (revelado ao clicar em coleção cheia) --}}
                    <div x-show="showItemLimitCta" x-cloak>
                        <x-collection-upgrade-cta
                            title="Limite de itens atingido"
                            description="Faça upgrade para inserir mais itens."
                            compact
                        />
                    </div>

                    <div x-show="!showItemLimitCta">
                    @if ($canCreate)
                        @if ($showCreate)
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <input
                                    wire:model="newTitle"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Nome da nova coleção"
                                    autofocus
                                    class="tw-flex-1 tw-rounded-md tw-border-slate-300 tw-text-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 @error('newTitle') tw-border-red-500 @enderror"
                                >
                                <button
                                    wire:click="createAndAdd"
                                    wire:loading.attr="disabled"
                                    class="tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 disabled:tw-opacity-60 tw-transition-colors"
                                >
                                    <span wire:loading.remove wire:target="createAndAdd">Criar</span>
                                    <span wire:loading wire:target="createAndAdd">…</span>
                                </button>
                                <button
                                    wire:click="$set('showCreate', false)"
                                    class="tw-text-sm tw-text-slate-500 hover:tw-text-slate-700 tw-shrink-0"
                                >
                                    Cancelar
                                </button>
                            </div>
                            @error('newTitle')
                                <p class="tw-mt-1 tw-text-xs tw-text-red-600">{{ $message }}</p>
                            @enderror
                        @else
                            <button
                                wire:click="$set('showCreate', true)"
                                class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 tw-transition-colors"
                            >
                                <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nova coleção
                            </button>
                        @endif
                    @else
                        {{-- Limite atingido: botão que revela CTA ao clicar --}}
                        <div x-data="{ showCta: false }">
                            <button
                                x-show="!showCta"
                                x-on:click="showCta = true"
                                class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-font-medium tw-text-slate-400 hover:tw-text-slate-600 tw-transition-colors"
                            >
                                <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nova coleção
                            </button>
                            <div x-show="showCta" x-cloak>
                                <x-collection-upgrade-cta
                                    title="Limite de coleções atingido"
                                    description="Faça upgrade para criar mais coleções."
                                    compact
                                />
                            </div>
                        </div>
                    @endif
                    </div>

                </div>

            </div>
        </div>
    @endif
</div>
