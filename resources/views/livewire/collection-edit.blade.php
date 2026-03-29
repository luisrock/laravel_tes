<div>
    {{-- Toast de sucesso --}}
    <div
        x-data="{ show: false }"
        x-on:collection-saved.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition:enter="tw-transition tw-ease-out tw-duration-200"
        x-transition:enter-start="tw-opacity-0 tw-translate-y-1"
        x-transition:enter-end="tw-opacity-100 tw-translate-y-0"
        x-transition:leave="tw-transition tw-ease-in tw-duration-150"
        x-transition:leave-start="tw-opacity-100"
        x-transition:leave-end="tw-opacity-0"
        x-cloak
        class="tw-mb-4 tw-bg-green-50 tw-border-l-4 tw-border-green-400 tw-p-4 tw-rounded-r-md tw-flex tw-items-center tw-gap-2"
    >
        <svg class="tw-w-4 tw-h-4 tw-text-green-500 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <p class="tw-text-sm tw-text-green-700">Coleção atualizada com sucesso.</p>
    </div>

    {{-- Dados da coleção --}}
    <div
        class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-mb-6"
        x-data="{
            dirty: false,
            initial: {},
            init() {
                this.initial = {
                    title: @js($title),
                    description: @js($description),
                    isPrivate: @js($isPrivate),
                };
                ['title', 'description', 'isPrivate'].forEach(prop => {
                    $wire.$watch(prop, () => {
                        this.dirty = $wire.title !== this.initial.title
                            || $wire.description !== this.initial.description
                            || $wire.isPrivate !== this.initial.isPrivate;
                    });
                });
            }
        }"
        x-on:collection-saved.window="dirty = false; initial = { title: $wire.title, description: $wire.description, isPrivate: $wire.isPrivate }"
    >
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50 tw-flex tw-items-center tw-justify-between">
            <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Dados da coleção</h3>
            @if (! $collection->is_private)
                <a
                    href="{{ route('colecoes.show', [$collection->user->name, $collection->slug]) }}"
                    target="_blank"
                    class="tw-text-xs tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline text-decoration-none tw-flex tw-items-center tw-gap-1"
                >
                    Ver página pública
                    <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            @endif
        </div>

        <div class="tw-p-6 tw-space-y-5 tw-max-w-xl">
            {{-- Título --}}
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">
                    Título <span class="tw-text-red-500">*</span>
                </label>
                <input
                    wire:model="title"
                    type="text"
                    maxlength="100"
                    class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('title') tw-border-red-500 @enderror"
                >
                @error('title')
                    <p class="tw-mt-1 tw-text-xs tw-text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Descrição --}}
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Descrição</label>
                <textarea
                    wire:model="description"
                    rows="3"
                    maxlength="500"
                    placeholder="Opcional. Máximo de 500 caracteres."
                    class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('description') tw-border-red-500 @enderror"
                ></textarea>
                @error('description')
                    <p class="tw-mt-1 tw-text-xs tw-text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Privacidade --}}
            <div x-data="{ showPrivacyCta: false }">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-2">Privacidade</label>
                <label class="tw-inline-flex tw-items-center tw-gap-2 tw-cursor-pointer">
                    @if ($limits['can_be_private'])
                        <input
                            wire:model="isPrivate"
                            type="checkbox"
                            class="tw-rounded tw-border-slate-300 tw-text-brand-600 focus:tw-ring-brand-500"
                        >
                    @else
                        <input
                            type="checkbox"
                            @click.prevent="showPrivacyCta = true"
                            class="tw-rounded tw-border-slate-300 tw-text-brand-600 focus:tw-ring-brand-500"
                        >
                    @endif
                    <span class="tw-text-sm tw-text-slate-700">Tornar esta coleção privada</span>
                </label>
                <p class="tw-mt-1 tw-text-xs tw-text-slate-500">
                    Coleções privadas ficam visíveis apenas para você.
                </p>
                @if (! $limits['can_be_private'])
                    <div x-show="showPrivacyCta" x-cloak class="tw-mt-3">
                        <x-collection-upgrade-cta
                            title="Privacidade exclusiva para assinantes"
                            description="Faça upgrade para criar coleções privadas."
                            compact
                        />
                    </div>
                @endif
            </div>

            {{-- Botão salvar --}}
            <div class="tw-pt-2">
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    :disabled="!dirty"
                    class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 disabled:tw-opacity-40 disabled:tw-cursor-not-allowed tw-transition-colors focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2"
                >
                    <span wire:loading.remove wire:target="save">Salvar alterações</span>
                    <span wire:loading wire:target="save">Salvando…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Toast de ordem salva --}}
    <div
        x-data="{ show: false }"
        x-on:reorder-saved.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition:enter="tw-transition tw-ease-out tw-duration-200"
        x-transition:enter-start="tw-opacity-0 tw-translate-y-1"
        x-transition:enter-end="tw-opacity-100 tw-translate-y-0"
        x-transition:leave="tw-transition tw-ease-in tw-duration-150"
        x-transition:leave-start="tw-opacity-100"
        x-transition:leave-end="tw-opacity-0"
        x-cloak
        class="tw-mb-4 tw-bg-green-50 tw-border-l-4 tw-border-green-400 tw-p-3 tw-rounded-r-md tw-flex tw-items-center tw-gap-2"
    >
        <svg class="tw-w-4 tw-h-4 tw-text-green-500 tw-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <p class="tw-text-sm tw-text-green-700">Nova ordem salva.</p>
    </div>

    {{-- Lista de itens --}}
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-mb-6">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50 tw-flex tw-items-center tw-gap-3">
            <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Itens da coleção</h3>
            <span class="tw-text-xs tw-bg-slate-100 tw-text-slate-600 tw-px-2 tw-py-0.5 tw-rounded-full tw-font-medium">
                {{ $itemsWithContent->count() }}
                @if ($limits['max_items'] !== -1)
                    de {{ $limits['max_items'] }}
                @endif
            </span>
        </div>

        @if ($itemsWithContent->isEmpty())
            <div class="tw-p-8 tw-text-center">
                <svg class="tw-mx-auto tw-h-10 tw-w-10 tw-text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="tw-mt-3 tw-text-sm tw-font-medium tw-text-slate-700">Nenhum item salvo ainda</p>
                <p class="tw-mt-1 tw-text-sm tw-text-slate-500">
                    Navegue pelas teses e súmulas e clique em "Salvar na coleção".
                </p>
                <a href="{{ url('/index') }}" class="tw-mt-3 tw-inline-flex tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">
                    Buscar teses e súmulas →
                </a>
            </div>
        @else
            <p class="tw-px-6 tw-pt-3 tw-text-xs tw-text-slate-400">Arraste para reordenar os itens.</p>
            <div
                wire:ignore
                x-data="{
                    init() {
                        new Sortable(this.$el, {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'tw-opacity-40',
                            onEnd: () => {
                                const order = [...this.$el.querySelectorAll('[data-item-id]')]
                                    .map(el => parseInt(el.dataset.itemId));
                                $wire.reorderItems(order);
                            }
                        });
                    }
                }"
                x-init="init()"
                class="tw-divide-y tw-divide-slate-100 tw-px-6"
            >
                @foreach ($itemsWithContent as $item)
                    <div wire:key="item-{{ $item->id }}" data-item-id="{{ $item->id }}" class="tw-flex tw-items-center tw-gap-3 tw-py-3">
                        {{-- Handle --}}
                        <span class="drag-handle tw-cursor-grab tw-text-slate-300 hover:tw-text-slate-500 tw-shrink-0" title="Arrastar para reordenar">
                            <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm8-12a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
                            </svg>
                        </span>

                        {{-- Badge tribunal --}}
                        <span class="tw-inline-flex tw-items-center tw-px-1.5 tw-py-0.5 tw-rounded tw-text-xs tw-font-bold tw-bg-brand-100 tw-text-brand-800 tw-shrink-0">
                            {{ $item->tribunal }}
                        </span>

                        {{-- Texto --}}
                        <span class="tw-flex-1 tw-text-sm tw-text-slate-700 tw-truncate tw-min-w-0">
                            {{ $item->label }}
                        </span>

                        {{-- Remover --}}
                        <button
                            wire:click="removeItem({{ $item->id }})"
                            wire:confirm="Remover este item da coleção?"
                            class="tw-text-slate-300 hover:tw-text-red-500 tw-transition-colors tw-shrink-0"
                            title="Remover item"
                        >
                            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Zona de perigo --}}
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-red-100">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-red-100 tw-bg-red-50/50">
            <h3 class="tw-text-base tw-font-semibold tw-text-red-800 tw-uppercase tw-tracking-wide">Excluir coleção</h3>
        </div>
        <div class="tw-px-6 tw-py-4 tw-flex tw-items-center tw-justify-between tw-gap-4">
            <p class="tw-text-sm tw-text-slate-600">
                Exclui permanentemente a coleção e todos os seus itens. Esta ação não pode ser desfeita.
            </p>
            <button
                wire:click="deleteCollection"
                wire:confirm="Excluir esta coleção permanentemente? Todos os itens serão removidos."
                class="tw-shrink-0 tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-red-300 tw-text-sm tw-font-medium tw-rounded-md tw-text-red-700 tw-bg-white hover:tw-bg-red-50 tw-transition-colors focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-red-500 focus:tw-ring-offset-2"
            >
                Excluir coleção
            </button>
        </div>
    </div>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    @endassets
</div>
