<div>
    {{-- Cabeçalho com contador e botão --}}
    <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200 tw-mb-6">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50 tw-flex tw-items-center tw-justify-between">
            <div class="tw-flex tw-items-center tw-gap-3">
                <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Suas coleções</h3>
                @if ($limits['max_collections'] !== -1)
                    <span class="tw-text-xs tw-bg-slate-100 tw-text-slate-600 tw-px-2 tw-py-0.5 tw-rounded-full tw-font-medium">
                        {{ $collections->count() }} de {{ $limits['max_collections'] }}
                    </span>
                @else
                    <span class="tw-text-xs tw-bg-slate-100 tw-text-slate-600 tw-px-2 tw-py-0.5 tw-rounded-full tw-font-medium">
                        {{ $collections->count() }} {{ $collections->count() === 1 ? 'coleção' : 'coleções' }}
                    </span>
                @endif
            </div>

            @if (! $showCreateForm && ! $showLimitCta)
                <button
                    wire:click="openCreateForm"
                    class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 tw-transition-colors focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2"
                >
                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova coleção
                </button>
            @endif
        </div>

        {{-- CTA inline de limite atingido --}}
        @if ($showLimitCta)
            <div class="tw-px-6 tw-py-5 tw-border-b tw-border-slate-100">
                <x-collection-upgrade-cta
                    title="Limite de coleções atingido"
                    description="Você atingiu o limite de coleções do seu plano. Faça upgrade para criar mais."
                />
                <button
                    wire:click="cancelCreate"
                    class="tw-mt-3 tw-text-sm tw-font-medium tw-text-slate-500 hover:tw-text-slate-700"
                >
                    Cancelar
                </button>
            </div>
        @endif

        {{-- Formulário inline de criação --}}
        @if ($showCreateForm)
            <div class="tw-px-6 tw-py-5 tw-border-b tw-border-slate-100 tw-bg-brand-50/40">
                <p class="tw-text-sm tw-font-semibold tw-text-slate-700 tw-mb-4">Nova coleção</p>
                <div class="tw-space-y-4 tw-max-w-lg">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">
                            Título <span class="tw-text-red-500">*</span>
                        </label>
                        <input
                            wire:model="newTitle"
                            type="text"
                            maxlength="100"
                            placeholder="Ex.: Direito do Trabalho — Horas Extras"
                            autofocus
                            class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('newTitle') tw-border-red-500 @enderror"
                        >
                        @error('newTitle')
                            <p class="tw-mt-1 tw-text-xs tw-text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1">Descrição</label>
                        <textarea
                            wire:model="newDescription"
                            rows="2"
                            maxlength="500"
                            placeholder="Opcional. Breve descrição da coleção."
                            class="tw-block tw-w-full tw-rounded-md tw-border-slate-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 sm:tw-text-sm @error('newDescription') tw-border-red-500 @enderror"
                        ></textarea>
                        @error('newDescription')
                            <p class="tw-mt-1 tw-text-xs tw-text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <button
                            wire:click="createCollection"
                            wire:loading.attr="disabled"
                            class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 disabled:tw-opacity-60 tw-transition-colors"
                        >
                            <span wire:loading.remove wire:target="createCollection">Criar coleção</span>
                            <span wire:loading wire:target="createCollection">Criando…</span>
                        </button>
                        <button
                            wire:click="cancelCreate"
                            class="tw-text-sm tw-font-medium tw-text-slate-600 hover:tw-text-slate-900"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Estado vazio --}}
        @if ($collections->isEmpty())
            <div class="tw-p-10 tw-text-center">
                <svg class="tw-mx-auto tw-h-12 tw-w-12 tw-text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <p class="tw-mt-3 tw-text-sm tw-font-medium tw-text-slate-700">Você ainda não tem coleções</p>
                <p class="tw-mt-1 tw-text-sm tw-text-slate-500">Organize teses e súmulas em coleções para acessar rapidamente.</p>
                @if (! $showCreateForm && ! $showLimitCta)
                    <button
                        wire:click="openCreateForm"
                        class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline"
                    >
                        Criar minha primeira coleção
                    </button>
                @endif
            </div>
        @else
            {{-- Grid de cards --}}
            <div class="tw-p-6 tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                @foreach ($collections as $collection)
                    <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-5 tw-flex tw-flex-col tw-gap-3 hover:tw-shadow-sm tw-transition-shadow">
                        <div class="tw-flex tw-items-start tw-justify-between tw-gap-2">
                            <h4 class="tw-text-sm tw-font-semibold tw-text-slate-800 tw-leading-snug">
                                {{ $collection->title }}
                            </h4>
                            @if ($collection->is_private)
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600 tw-px-2 tw-py-0.5 tw-rounded-full tw-shrink-0">
                                    <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Privada
                                </span>
                            @else
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-font-medium tw-bg-green-50 tw-text-green-700 tw-px-2 tw-py-0.5 tw-rounded-full tw-shrink-0">
                                    <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                                    </svg>
                                    Pública
                                </span>
                            @endif
                        </div>

                        @if ($collection->description)
                            <p class="tw-text-xs tw-text-slate-500 tw-line-clamp-2">{{ $collection->description }}</p>
                        @endif

                        <p class="tw-text-xs tw-text-slate-400">
                            {{ $collection->items_count }} {{ $collection->items_count === 1 ? 'item' : 'itens' }}
                            @if ($limits['max_items'] !== -1)
                                de {{ $limits['max_items'] }}
                            @endif
                        </p>

                        <div class="tw-flex tw-items-center tw-gap-3 tw-pt-1 tw-border-t tw-border-slate-100">
                            <a
                                href="{{ route('colecoes.edit', $collection->id) }}"
                                class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline text-decoration-none"
                            >
                                Editar
                            </a>
                            <button
                                wire:click="deleteCollection({{ $collection->id }})"
                                wire:confirm="Excluir '{{ addslashes($collection->title) }}'? Todos os itens serão removidos. Esta ação não pode ser desfeita."
                                class="tw-text-sm tw-font-medium tw-text-red-500 hover:tw-text-red-700"
                            >
                                Excluir
                            </button>
                            @if (! $collection->is_private)
                                <a
                                    href="{{ route('colecoes.show', [$collection->user->name ?? auth()->user()->name, $collection->slug]) }}"
                                    target="_blank"
                                    class="tw-ml-auto tw-text-xs tw-text-slate-400 hover:tw-text-slate-600 hover:tw-underline text-decoration-none"
                                >
                                    Ver pública ↗
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
