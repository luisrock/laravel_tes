@extends('front.base')

@section('page-title', 'Newsletters do T&S')

@section('content')

    <!-- Page Content -->

    <!-- Page Content -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">Newsletters do T&S</h1>
            <p class="tw-text-slate-600 tw-text-lg tw-leading-relaxed tw-m-0">
                Faça uma <a href="{{ route('searchpage') }}" class="tw-text-brand-600 hover:tw-text-brand-800 tw-font-medium hover:tw-underline">pesquisa</a> ou veja as
                <a href="{{ route('alltemaspage') }}" class="tw-text-brand-600 hover:tw-text-brand-800 tw-font-medium hover:tw-underline">pesquisas prontas</a>.
            </p>
        </section>
    </div>

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 tw-pb-10" id="content-results">

        <!-- Results -->

        <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
            <div class="tw-p-6 md:tw-p-8">
                
                @php
                    $user = auth()->user();
                @endphp

                <div class="tw-flex tw-w-full tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between md:tw-gap-8 tw-gap-3 tw-mb-6 tw-pb-4 tw-border-b tw-border-slate-100">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-shrink-0">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">Arquivo</span>
                        <span class="tw-text-slate-600 tw-font-medium">Newsletters</span>
                    </div>

                    @if ($integrationEnabled)
                        <div class="tw-w-full md:tw-w-auto md:tw-shrink-0 md:tw-ml-auto">
                            @if ($isAlreadySubscribed)
                                <p class="tw-text-sm tw-font-medium tw-text-emerald-700 tw-m-0 md:tw-text-right">
                                    Você está inscrito!
                                    <a href="{{ route('user-panel.profile') }}" class="tw-font-normal tw-underline">Gerir em Perfil</a>
                                </p>
                            @elseif ($user)
                                <div x-data="newsletterQuickSubscribe()" class="tw-flex tw-flex-col tw-items-start md:tw-items-end tw-gap-1">
                                    <button type="button" @click="subscribe()" :disabled="loading"
                                            class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline disabled:tw-opacity-50 disabled:tw-cursor-wait tw-bg-transparent tw-border-0 tw-p-0">
                                        <span x-show="!loading">Receba atualização semanal</span>
                                        <span x-show="loading" x-cloak>Inscrevendo…</span>
                                    </button>
                                    <p x-show="message" x-text="message" x-cloak
                                       :class="success ? 'tw-text-emerald-700' : 'tw-text-rose-700'"
                                       class="tw-text-xs tw-m-0 md:tw-text-right"></p>
                                </div>
                            @else
                                <div x-data="newsletterForm()" x-init="init()" class="tw-flex tw-flex-col tw-items-start md:tw-items-end tw-gap-1">
                                    <form @submit.prevent="submit($event)" class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-end tw-gap-1.5" novalidate>
                                        @csrf
                                        @honeypot
                                        <label class="tw-sr-only" for="newsletter-name">Nome</label>
                                        <input id="newsletter-name" type="text" name="name" required maxlength="255"
                                               placeholder="Nome"
                                               x-model="name"
                                               class="tw-block tw-w-full sm:tw-w-36 tw-rounded tw-border tw-border-slate-300 tw-px-2 tw-py-1.5 tw-text-xs tw-text-slate-900 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-brand-500 focus:tw-border-brand-500">
                                        <label class="tw-sr-only" for="newsletter-email">E-mail</label>
                                        <input id="newsletter-email" type="email" name="email" required maxlength="255"
                                               placeholder="E-mail"
                                               x-model="email"
                                               class="tw-block tw-w-full sm:tw-w-44 tw-rounded tw-border tw-border-slate-300 tw-px-2 tw-py-1.5 tw-text-xs tw-text-slate-900 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-brand-500 focus:tw-border-brand-500">
                                        <button type="submit" :disabled="loading"
                                                class="tw-shrink-0 tw-rounded tw-bg-brand-600 tw-px-3 tw-py-1.5 tw-text-xs tw-font-medium tw-text-white hover:tw-bg-brand-700 disabled:tw-opacity-50">
                                            <span x-show="!loading">Receba</span>
                                            <span x-show="loading" x-cloak>…</span>
                                        </button>
                                    </form>
                                    <p x-show="message" x-text="message" x-cloak
                                       :class="success ? 'tw-text-emerald-700' : 'tw-text-rose-700'"
                                       class="tw-text-xs tw-m-0 md:tw-text-right"></p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="tw-space-y-4">
                    @foreach ($campaigns as $campaign)
                        <div class="newsletter-item tw-block tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-6 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all">
                            <h4 class="tw-text-lg tw-font-semibold tw-text-slate-900 tw-mb-2">
                                <a href="{{ route('newsletter.show', $campaign->slug) }}" class="tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline tw-underline-offset-2">
                                    {{ $campaign->subject }}
                                </a>
                            </h4>

                            <p class="tw-text-slate-600 tw-mb-4 tw-line-clamp-2">
                                {{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($campaign->plain_text ?? $campaign->html_content)), 200) }}
                            </p>

                            <div class="tw-flex tw-justify-between tw-items-center tw-pt-3 tw-border-t tw-border-slate-50">
                                <span class="tw-text-xs tw-font-medium tw-text-slate-400">
                                    <i class="fa fa-calendar-alt tw-mr-1"></i>
                                    Envio: {{ $campaign->sent_at ? $campaign->sent_at->format('d/m/Y') : '' }}
                                </span>
                                <a href="{{ route('newsletter.show', $campaign->slug) }}" class="tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800">
                                    Ler mais <i class="fa fa-arrow-right tw-ml-1 tw-text-xs"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="tw-mt-8">
                    {{ $campaigns->links() }} 
                    {{-- Note: Standard Laravel pagination might use Bootstrap classes. 
                         If so, we might need to publish pagination views or customize them to Tailwind.
                         Laravel has a built-in tailwind pagination view: $campaigns->links('pagination::tailwind') --}}
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
    @if ($integrationEnabled && ! $isAlreadySubscribed)
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
        <script>
            function newsletterForm() {
                return {
                    name: '',
                    email: '',
                    loading: false,
                    message: '',
                    success: false,
                    init() {},
                    async submit(event) {
                        this.loading = true;
                        this.message = '';
                        const formData = new FormData(event.target);
                        try {
                            const res = await fetch(@json(route('newsletter.subscribe')), {
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
                                setTimeout(() => window.location.reload(), 1200);
                            }
                        } catch (e) {
                            this.success = false;
                            this.message = 'Erro de rede. Tente em instantes.';
                        } finally {
                            this.loading = false;
                        }
                    },
                };
            }

            function newsletterQuickSubscribe() {
                return {
                    name: @json(auth()->user()?->name ?? ''),
                    email: @json(auth()->user()?->email ?? ''),
                    loading: false,
                    message: '',
                    success: false,
                    async subscribe() {
                        this.loading = true;
                        this.message = '';
                        const formData = new FormData();
                        formData.append('name', this.name);
                        formData.append('email', this.email);
                        formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
                        try {
                            const res = await fetch(@json(route('newsletter.subscribe')), {
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
                                setTimeout(() => window.location.reload(), 1200);
                            }
                        } catch (e) {
                            this.success = false;
                            this.message = 'Erro de rede. Tente em instantes.';
                        } finally {
                            this.loading = false;
                        }
                    },
                };
            }
        </script>
    @endif
@endsection
