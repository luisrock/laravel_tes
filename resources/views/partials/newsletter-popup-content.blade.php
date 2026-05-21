@php
    $popupConfig = [
        'trigger' => \App\Models\SiteSetting::get('newsletter_popup_trigger', 'timer'),
        'delaySeconds' => (int) \App\Models\SiteSetting::get('newsletter_popup_delay_seconds', '20'),
        'scrollPercent' => (int) \App\Models\SiteSetting::get('newsletter_popup_scroll_percent', '50'),
        'frequencyDays' => (int) \App\Models\SiteSetting::get('newsletter_popup_frequency_days', '14'),
        'dismissResetEpoch' => (int) \App\Models\SiteSetting::get('newsletter_popup_dismiss_reset_epoch', '0'),
        'subscribedResetEpoch' => (int) \App\Models\SiteSetting::get('newsletter_popup_subscribed_reset_epoch', '0'),
        'variantA' => [
            'title' => \App\Models\SiteSetting::get('newsletter_popup_variant_a_title', 'Acompanhe as decisões mais importantes'),
            'body' => \App\Models\SiteSetting::get('newsletter_popup_variant_a_body', 'Receba semanalmente um resumo dos novos repetitivos e súmulas dos tribunais superiores.'),
            'cta' => \App\Models\SiteSetting::get('newsletter_popup_variant_a_cta', 'Quero receber'),
        ],
        'variantBEnabled' => \App\Models\SiteSetting::getAsBool('newsletter_popup_variant_b_enabled', false),
        'variantB' => [
            'title' => \App\Models\SiteSetting::get('newsletter_popup_variant_b_title', ''),
            'body' => \App\Models\SiteSetting::get('newsletter_popup_variant_b_body', ''),
            'cta' => \App\Models\SiteSetting::get('newsletter_popup_variant_b_cta', ''),
        ],
        'splitPercent' => (int) \App\Models\SiteSetting::get('newsletter_popup_split_percent', '50'),
        'subscribeUrl' => route('newsletter.subscribe'),
        'eventUrl' => route('newsletter.event'),
    ];
@endphp

<div
    id="newsletter-popup-root"
    x-data="newsletterPopup(@js($popupConfig))"
    x-init="init()"
    x-cloak
    class="tw-contents"
>
    <div
        x-show="open"
        x-transition:enter="tw-transition tw-ease-out tw-duration-200"
        x-transition:enter-start="tw-opacity-0"
        x-transition:enter-end="tw-opacity-100"
        x-transition:leave="tw-transition tw-ease-in tw-duration-150"
        x-transition:leave-start="tw-opacity-100"
        x-transition:leave-end="tw-opacity-0"
        class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="newsletter-popup-title"
        data-testid="newsletter-popup"
    >
        <div class="tw-absolute tw-inset-0 tw-bg-slate-950/65 tw-backdrop-blur-[2px]" @click="dismiss()" aria-hidden="true"></div>

        <div
            class="tw-relative tw-z-10 tw-w-full tw-max-w-md tw-overflow-hidden tw-rounded-2xl tw-border-2 tw-border-brand-400/40 tw-bg-white tw-shadow-2xl tw-shadow-brand-900/25 tw-ring-4 tw-ring-white/10"
            @click.stop
        >
            <div class="tw-bg-gradient-to-br tw-from-brand-800 tw-via-brand-700 tw-to-brand-600 tw-px-5 tw-pt-5 tw-pb-4">
                <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                    <div class="tw-flex tw-min-w-0 tw-flex-1 tw-items-start tw-gap-3">
                        <span
                            class="tw-mt-0.5 tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-full tw-bg-white/15 tw-text-white tw-ring-1 tw-ring-white/25"
                            aria-hidden="true"
                        >
                            <i class="fa fa-envelope-o tw-text-base"></i>
                        </span>
                        <div class="tw-min-w-0 tw-flex-1">
                            <p class="tw-mb-1 tw-text-[11px] tw-font-semibold tw-uppercase tw-tracking-wider tw-text-brand-100/90">
                                Newsletter T&amp;S
                            </p>
                            <h2
                                id="newsletter-popup-title"
                                class="tw-text-lg tw-font-semibold tw-leading-snug tw-text-white"
                                x-text="title"
                            ></h2>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="dismiss()"
                        class="tw-mt-0.5 tw-flex tw-h-9 tw-w-9 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-text-white/90 hover:tw-bg-white/15 hover:tw-text-white focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-white/40"
                        aria-label="Fechar"
                    >
                        <i class="fa fa-times tw-text-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <p class="tw-mt-3 tw-pl-[3.25rem] tw-text-sm tw-leading-relaxed tw-text-brand-50/95" x-text="body"></p>
            </div>

            <div class="tw-border-t tw-border-brand-200/50 tw-bg-gradient-to-b tw-from-slate-50 tw-to-white tw-px-5 tw-py-5">
                <form @submit.prevent="submit($event)" class="tw-space-y-3" novalidate>
                    @csrf
                    @honeypot
                    <label class="tw-block">
                        <span class="tw-sr-only">Nome</span>
                        <input
                            type="text"
                            name="name"
                            required
                            maxlength="255"
                            placeholder="Seu nome"
                            x-model="name"
                            class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 tw-shadow-sm placeholder:tw-text-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500/20"
                        >
                    </label>
                    <label class="tw-block">
                        <span class="tw-sr-only">E-mail</span>
                        <input
                            type="email"
                            name="email"
                            required
                            maxlength="255"
                            placeholder="Seu e-mail"
                            x-model="email"
                            class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 tw-shadow-sm placeholder:tw-text-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500/20"
                        >
                    </label>
                    <button
                        type="submit"
                        :disabled="loading || submitting"
                        class="tw-w-full tw-rounded-lg tw-bg-brick-600 tw-px-4 tw-py-3 tw-text-sm tw-font-semibold tw-text-white tw-shadow-lg tw-shadow-brick-600/30 hover:tw-bg-brick-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brick-500/40 disabled:tw-opacity-50"
                        x-text="cta"
                    ></button>
                    <p
                        x-show="message"
                        x-text="message"
                        x-cloak
                        :class="success ? 'tw-text-emerald-700' : 'tw-text-rose-700'"
                        class="tw-text-sm"
                    ></p>
                </form>

                <button
                    type="button"
                    @click="dismiss()"
                    class="tw-mt-4 tw-w-full tw-text-center tw-text-xs tw-font-medium tw-text-slate-500 hover:tw-text-brand-700 hover:tw-underline"
                >
                    Agora não
                </button>
            </div>
        </div>
    </div>
</div>

@once('alpinejs-3.14.3')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
@endonce
<script>
    function newsletterPopup(config) {
        return {
            open: false,
            variant: 'A',
            title: '',
            body: '',
            cta: '',
            name: '',
            email: '',
            loading: false,
            submitting: false,
            message: '',
            success: false,
            armed: false,

            init() {
                if (this.shouldSkip()) {
                    return;
                }
                this.pickVariant();
                this.applyVariantCopy();
                this.armTrigger();
            },

            shouldSkip() {
                if (this.isSubscribedCookieActive()) {
                    return true;
                }

                const until = this.getCookie('newsletter_popup_dismissed_until');
                const dismissEpoch = this.getCookie('newsletter_popup_dismiss_epoch');
                if (
                    until
                    && parseInt(until, 10) > Date.now()
                    && dismissEpoch === String(config.dismissResetEpoch)
                ) {
                    return true;
                }

                return false;
            },

            isSubscribedCookieActive() {
                const subscribed = this.getCookie('newsletter_subscribed');
                const subscribedEpoch = this.getCookie('newsletter_popup_subscribed_epoch');

                return subscribed === '1' && subscribedEpoch === String(config.subscribedResetEpoch);
            },

            pickVariant() {
                const stored = this.getCookie('newsletter_popup_variant');
                if (stored === 'A' || stored === 'B') {
                    this.variant = stored;

                    return;
                }

                if (!config.variantBEnabled) {
                    this.variant = 'A';
                } else {
                    this.variant = Math.random() * 100 < config.splitPercent ? 'B' : 'A';
                }

                this.setCookie('newsletter_popup_variant', this.variant, 365);
            },

            applyVariantCopy() {
                const copy = this.variant === 'B' && config.variantBEnabled
                    ? config.variantB
                    : config.variantA;
                this.title = copy.title || config.variantA.title;
                this.body = copy.body || config.variantA.body;
                this.cta = copy.cta || config.variantA.cta;
            },

            armTrigger() {
                if (config.trigger === 'timer') {
                    setTimeout(() => this.maybeOpen(), config.delaySeconds * 1000);

                    return;
                }

                if (config.trigger === 'exit_intent') {
                    const handler = (event) => {
                        if (this.armed || this.open) {
                            return;
                        }
                        if (event.clientY <= 0) {
                            this.armed = true;
                            document.removeEventListener('mouseout', handler);
                            this.maybeOpen();
                        }
                    };
                    document.addEventListener('mouseout', handler);

                    return;
                }

                if (config.trigger === 'scroll') {
                    const onScroll = () => {
                        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                        if (docHeight <= 0) {
                            return;
                        }
                        const scrolled = (window.scrollY / docHeight) * 100;
                        if (scrolled >= config.scrollPercent && !this.armed) {
                            this.armed = true;
                            window.removeEventListener('scroll', onScroll);
                            this.maybeOpen();
                        }
                    };
                    window.addEventListener('scroll', onScroll, { passive: true });
                }
            },

            maybeOpen() {
                if (this.shouldSkip() || this.open) {
                    return;
                }
                this.open = true;
                this.trackEvent('impression');
            },

            dismiss() {
                const until = Date.now() + (config.frequencyDays * 24 * 60 * 60 * 1000);
                this.setCookie('newsletter_popup_dismissed_until', String(until), config.frequencyDays);
                this.setCookie('newsletter_popup_dismiss_epoch', String(config.dismissResetEpoch), config.frequencyDays);
                this.trackEvent('dismissed');
                this.open = false;
            },

            async submit(event) {
                if (this.submitting || this.loading) {
                    return;
                }

                this.submitting = true;
                this.loading = true;
                this.message = '';
                const formData = new FormData(event.target);
                formData.append('from_popup', '1');
                formData.append('popup_variant', this.variant);
                formData.append('popup_trigger', config.trigger);

                try {
                    const res = await fetch(config.subscribeUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await res.json();
                    this.success = !!data.success || !!data.already_subscribed;
                    this.message = data.message ?? 'Não foi possível inscrever agora.';
                    if (this.success) {
                        this.setCookie('newsletter_subscribed', '1', 365);
                        this.setCookie('newsletter_popup_subscribed_epoch', String(config.subscribedResetEpoch), 365);
                        setTimeout(() => {
                            this.open = false;
                        }, 1500);
                    }
                } catch (e) {
                    this.success = false;
                    this.message = 'Erro de rede. Tente em instantes.';
                    this.loading = false;
                    this.submitting = false;
                }
            },

            trackEvent(action) {
                fetch(config.eventUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action,
                        variant: this.variant,
                        trigger: config.trigger,
                    }),
                }).catch(() => {});
            },

            getCookie(name) {
                const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));

                return match ? decodeURIComponent(match[1]) : null;
            },

            setCookie(name, value, days) {
                const expires = new Date(Date.now() + days * 864e5).toUTCString();
                document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/; SameSite=Lax';
            },
        };
    }
</script>
