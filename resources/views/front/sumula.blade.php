@extends('front.base')

@section('page-title', $label)

@section('content')

    <!-- Page Content -->

    <!-- Page Content -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
                 <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-brand-100 tw-text-brand-800">Súmula</span>
                 <span class="tw-text-slate-500 tw-text-sm">{{ $tribunal }}</span>
            </div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">{{ $sumula->titulo }}</h1>
            @if(isset($sumula->isCancelada) && $sumula->isCancelada)
                <div class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-red-100 tw-text-red-800">
                    <i class="fa fa-ban"></i> CANCELADO / REVOGADA
                </div>
            @endif
        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4 tw-pb-2">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10" id="content-results">

        <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
            <div class="tw-p-6 md:tw-p-8">
                
                <!-- Tools -->
                <div class="tw-flex tw-flex-wrap tw-gap-3 tw-mb-6 tw-justify-end">
                     <button class="btn-copy-text" data-clipboard-text="{{ $sumula->to_be_copied }}">
                        <i class="fa fa-copy tw-mr-1.5"></i> <span class="btn-text">Copiar Súmula</span>
                    </button>
                    <!-- WhatsApp Share -->
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($sumula->to_be_copied . ' ' . Request::url()) }}" 
                       target="_blank"
                       class="tw-inline-flex tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-green-200 tw-text-green-700 hover:tw-bg-green-50 tw-px-3 tw-py-1.5 tw-text-sm tw-transition">
                        <i class="fab fa-whatsapp tw-mr-1.5"></i> Compartilhar
                    </a>
                </div>

                <!-- Content -->
                <div class="tw-prose tw-prose-slate tw-max-w-none tw-mb-8">
                    <p class="tw-text-xl tw-font-serif tw-text-slate-800 tw-leading-relaxed">
                        {{ $sumula->texto }}
                    </p>
                </div>

                <!-- Meta Info Grid -->
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6 tw-bg-slate-50 tw-rounded-lg tw-p-6 tw-border tw-border-slate-200">
                    
                    @if(!empty($sumula->obs))
                    <div>
                        <h5 class="tw-text-sm tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-2">
                            Referência Legislativa/Observações
                        </h5>
                        <div class="tw-text-slate-700 tw-text-sm">
                            {{ $sumula->obs }}
                        </div>
                    </div>
                    @endif

                    @if(!empty($sumula->precedentes))
                    <div>
                        <h5 class="tw-text-sm tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-2">
                            Precedentes
                        </h5>
                        <div class="tw-text-slate-700 tw-text-sm tw-whitespace-pre-line">
                            {{ $sumula->precedentes }}
                        </div>
                    </div>
                    @endif

                    @if(!empty($sumula->tempo))
                    <div class="md:tw-col-span-2">
                        <h5 class="tw-text-sm tw-font-semibold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-2">
                            Data
                        </h5>
                        <div class="tw-text-slate-700 tw-text-sm">
                            {{ $sumula->tempo }}
                        </div>
                    </div>
                    @endif

                    @if(!empty($sumula->link))
                    <div class="md:tw-col-span-2 tw-text-right">
                        <a href="{{ $sumula->link }}" target="_blank" class="tw-inline-flex tw-items-center tw-gap-1 tw-text-brand-600 hover:tw-text-brand-800 tw-font-medium tw-text-sm">
                            Ver no site do {{ $tribunal }} <i class="fa fa-external-link-alt tw-text-xs"></i>
                        </a>
                    </div>
                    @endif
                </div>

            </div>
        </div>
        
        <!-- Admin Actions -->
        @if($admin)
        <div class="tw-mt-4 tw-text-right">
            {{-- Add admin actions here if needed, keeping simple for now based on public view requirements --}}
        </div>
        @endif

    </div>

@endsection
