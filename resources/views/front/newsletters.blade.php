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
                
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-gap-4 tw-mb-6 tw-pb-4 tw-border-b tw-border-slate-100">
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">Arquivo</span>
                        <span class="tw-text-slate-600 tw-font-medium">Newsletters</span>
                    </div>
                    <div class="tw-text-sm tw-text-slate-600">
                        Inscreva-se <a class="tw-text-brand-600 hover:tw-text-brand-800 tw-font-medium hover:tw-underline"
                                href="https://newsletter.maurolopes.com.br/subscription?f=guJ3cS2Vm7AxAFSQ24hY1x2LOVOrbH44BBFmy4NXDEULQPmQ9VecJ538XJVLM9JbWogaBgUkTuwWL1y8WaWG1w">aqui</a> para receber por email.
                    </div>
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
