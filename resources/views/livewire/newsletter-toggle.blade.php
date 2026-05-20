<div>
    @if (! \App\Models\SiteSetting::getAsBool('newsletter_integration_enabled', false))
        <p class="tw-text-sm tw-text-slate-500 tw-m-0">As preferências de newsletter estarão disponíveis em breve.</p>
    @else
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
            <div>
                <p class="tw-font-medium tw-text-slate-800 tw-m-0">Email semanal de atualização em Teses &amp; Súmulas</p>
                <p class="tw-text-sm tw-text-slate-500 tw-mt-1 tw-m-0">
                    {{ $subscribed ? 'Você está inscrito na newsletter.' : 'Você não está inscrito na newsletter.' }}
                </p>
            </div>

            @if ($subscribed)
                <button type="button" wire:loading.attr="disabled" wire:click="$dispatch('confirm-unsubscribe')"
                        x-on:confirm-unsubscribe.window="if (confirm('Tem certeza de que quer parar de receber email semanal de atualização em teses e súmulas dos tribunais superiores?')) { $wire.unsubscribe() }"
                        class="tw-shrink-0 tw-rounded-md tw-border tw-border-rose-300 tw-bg-white tw-px-3 tw-py-1.5 tw-text-sm tw-text-rose-700 hover:tw-bg-rose-50 disabled:tw-opacity-50">
                    Sair da lista
                </button>
            @else
                <button type="button" wire:loading.attr="disabled" wire:click="subscribe"
                        class="tw-shrink-0 tw-rounded-md tw-bg-brand-600 tw-px-3 tw-py-1.5 tw-text-sm tw-font-medium tw-text-white hover:tw-bg-brand-700 disabled:tw-opacity-50">
                    Entrar na lista
                </button>
            @endif
        </div>

        @if ($message)
            <p class="tw-mt-3 tw-text-sm tw-m-0 {{ $messageType === 'success' ? 'tw-text-emerald-700' : 'tw-text-rose-700' }}">
                {{ $message }}
            </p>
        @endif

        <div wire:loading wire:target="subscribe,unsubscribe" class="tw-mt-2 tw-text-xs tw-text-slate-500">
            Atualizando…
        </div>
    @endif
</div>
