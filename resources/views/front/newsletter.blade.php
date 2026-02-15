@extends('front.base')

@section('page-title', $campaign->subject)

@section('content')

    <!-- Page Content -->

    <!-- Page Content -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-3 tw-mb-2">
                 <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">Newsletter</span>
                 @if($campaign->sent_at)
                    <span class="tw-text-slate-500 tw-text-sm">Enviada em {{ $campaign->sent_at->format('d/m/Y') }}</span>
                 @endif
            </div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">{{ $campaign->subject }}</h1>
        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4 tw-pb-2">
        <x-breadcrumb :items="[
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Newsletters', 'url' => route('newsletterspage')],
            ['name' => $campaign->subject, 'url' => null]
        ]" />
    </div>
    <!-- END Breadcrumb -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10" id="content-results">

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
            
            <!-- Main Content -->
            <div class="lg:tw-col-span-2">
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-p-6 md:tw-p-8">
                         <div class="tw-prose tw-prose-slate tw-max-w-none">
                            {!! $campaign->html_content !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="tw-space-y-6">
                 <!-- Subscribe Box -->
                 <div class="tw-bg-brand-50 tw-rounded-xl tw-border tw-border-brand-100 tw-overflow-hidden tw-p-6">
                    <h3 class="tw-text-lg tw-font-bold tw-text-brand-900 tw-mb-2">Gostou?</h3>
                    <p class="tw-text-brand-800 tw-text-sm tw-mb-4">
                        Receba nossas newsletters diretamente no seu email. É gratuito e você pode cancelar quando quiser.
                    </p>
                    <a href="https://newsletter.maurolopes.com.br/subscription?f=guJ3cS2Vm7AxAFSQ24hY1x2LOVOrbH44BBFmy4NXDEULQPmQ9VecJ538XJVLM9JbWogaBgUkTuwWL1y8WaWG1w" 
                       target="_blank"
                       class="tw-block tw-w-full tw-text-center tw-rounded-lg tw-bg-brand-600 tw-text-white hover:tw-bg-brand-700 tw-px-4 tw-py-2.5 tw-font-medium tw-transition hover:tw-shadow-md">
                        Inscrever-se Agora
                    </a>
                </div>

                <!-- Share -->
                 <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden tw-p-6">
                    <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4">Compartilhar</h4>
                    <div class="tw-space-y-3">
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($campaign->subject . ' ' . Request::url()) }}" 
                           target="_blank"
                           class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-bg-green-600 tw-text-white hover:tw-bg-green-700 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                            <i class="fab fa-whatsapp tw-mr-2"></i> WhatsApp
                        </a>
                         <a href="https://twitter.com/intent/tweet?text={{ urlencode($campaign->subject . ' ' . Request::url()) }}" 
                           target="_blank"
                           class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-bg-sky-500 tw-text-white hover:tw-bg-sky-600 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                            <i class="fab fa-twitter tw-mr-2"></i> Twitter
                        </a>
                         <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(Request::url()) }}" 
                           target="_blank"
                           class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-bg-blue-600 tw-text-white hover:tw-bg-blue-700 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                            <i class="fab fa-facebook-f tw-mr-2"></i> Facebook
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
