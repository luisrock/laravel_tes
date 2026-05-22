@if ($integrationEnabled)
    <div class="tw-bg-brand-50 tw-rounded-xl tw-border tw-border-brand-100 tw-overflow-hidden tw-p-6">
        <h3 class="tw-text-lg tw-font-bold tw-text-brand-900 tw-mb-2">Gostou?</h3>
        <p class="tw-text-brand-800 tw-text-sm tw-mb-4">
            Receba nossas newsletters diretamente no seu email. É gratuito e você pode cancelar quando quiser.
        </p>

        @if ($isAlreadySubscribed)
            <p class="tw-text-sm tw-font-medium tw-text-emerald-700 tw-m-0">
                Você está inscrito!
                <a href="{{ route('user-panel.profile') }}" class="tw-font-normal tw-underline">Gerir em Perfil</a>
            </p>
        @elseif (auth()->user())
            <div x-data="newsletterQuickSubscribe()" class="tw-space-y-2">
                <button type="button" @click="subscribe()" :disabled="loading"
                        class="tw-block tw-w-full tw-text-center tw-rounded-lg tw-bg-brand-600 tw-text-white hover:tw-bg-brand-700 tw-px-4 tw-py-2.5 tw-font-medium tw-transition hover:tw-shadow-md disabled:tw-opacity-50 disabled:tw-cursor-wait">
                    <span x-show="!loading">Inscrever-se Agora</span>
                    <span x-show="loading" x-cloak>Inscrevendo…</span>
                </button>
                <p x-show="message" x-text="message" x-cloak
                   :class="success ? 'tw-text-emerald-700' : 'tw-text-rose-700'"
                   class="tw-text-xs tw-m-0 tw-text-center"></p>
            </div>
        @else
            <div x-data="newsletterForm()" x-init="init()" class="tw-space-y-3">
                <form @submit.prevent="submit($event)" class="tw-space-y-3" novalidate>
                    @csrf
                    @honeypot
                    <div>
                        <label class="tw-sr-only" for="newsletter-sidebar-name">Nome</label>
                        <input id="newsletter-sidebar-name" type="text" name="name" required maxlength="255"
                               placeholder="Seu nome"
                               x-model="name"
                               class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-brand-200 tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500">
                    </div>
                    <div>
                        <label class="tw-sr-only" for="newsletter-sidebar-email">E-mail</label>
                        <input id="newsletter-sidebar-email" type="email" name="email" required maxlength="255"
                               placeholder="Seu e-mail"
                               x-model="email"
                               class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-brand-200 tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500">
                    </div>
                    <button type="submit" :disabled="loading"
                            class="tw-block tw-w-full tw-text-center tw-rounded-lg tw-bg-brand-600 tw-text-white hover:tw-bg-brand-700 tw-px-4 tw-py-2.5 tw-font-medium tw-transition hover:tw-shadow-md disabled:tw-opacity-50">
                        <span x-show="!loading">Inscrever-se Agora</span>
                        <span x-show="loading" x-cloak>Inscrevendo…</span>
                    </button>
                </form>
                <p x-show="message" x-text="message" x-cloak
                   :class="success ? 'tw-text-emerald-700' : 'tw-text-rose-700'"
                   class="tw-text-xs tw-m-0 tw-text-center"></p>
            </div>
        @endif
    </div>
@endif
