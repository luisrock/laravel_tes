@extends('front.base')

@section('page-title', 'Súmulas de Tribunais')

@section('content')

    <!-- Page Content -->

    <!-- Page Content -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">Índice de Súmulas e Teses</h1>
            <p class="tw-text-slate-600 tw-text-lg tw-leading-relaxed tw-m-0">
                Acesse diretamente as coleções por tribunal com navegação rápida e objetiva.
            </p>
        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4 tw-pb-2">
        <x-breadcrumb :items="[
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => null]
        ]" />
    </div>
    <!-- END Breadcrumb -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10" id="content-results">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
            
            <!-- Súmulas Card -->
            <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-border tw-border-slate-200 tw-h-full hover:tw-shadow-md tw-transition-shadow">
                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-5">
                    <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-brand-50 tw-flex tw-items-center tw-justify-center tw-text-brand-600">
                        <i class="fa fa-gavel tw-text-xl"></i>
                    </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-slate-800 tw-m-0">Súmulas</h2>
                </div>
                
                <div class="tw-space-y-3">
                    <a href="{{ route('stfallsumulaspage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Súmulas do Supremo Tribunal Federal (STF)</span>
                    </a>
                    <a href="{{ route('stjallsumulaspage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Súmulas do Superior Tribunal de Justiça (STJ)</span>
                    </a>
                    <a href="{{ route('tstallsumulaspage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Súmulas do Tribunal Superior do Trabalho (TST)</span>
                    </a>
                    <a href="{{ route('tnuallsumulaspage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Súmulas da Turma Nacional de Uniformização (TNU)</span>
                    </a>
                </div>
            </section>

            <!-- Teses Card -->
            <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-border tw-border-slate-200 tw-h-full hover:tw-shadow-md tw-transition-shadow">
                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-5">
                    <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-brand-50 tw-flex tw-items-center tw-justify-center tw-text-brand-600">
                        <i class="fa fa-book tw-text-xl"></i>
                    </div>
                    <h2 class="tw-text-xl tw-font-bold tw-text-slate-800 tw-m-0">Teses Vinculantes</h2>
                </div>
                
                <div class="tw-space-y-3">
                    <a href="{{ route('stfalltesespage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Teses de Repercussão Geral do STF</span>
                    </a>
                    <a href="{{ route('stjalltesespage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Teses Repetitivas do STJ</span>
                    </a>
                    <a href="{{ route('tstalltesespage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Teses Vinculantes do TST</span>
                    </a>
                    <a href="{{ route('tnualltesespage') }}" class="tw-group tw-flex tw-items-center tw-gap-2 tw-p-3 tw-rounded-lg hover:tw-bg-slate-50 tw-transition-colors tw-text-slate-700 hover:tw-text-brand-700">
                        <i class="fa fa-chevron-right tw-text-xs tw-text-slate-400 group-hover:tw-text-brand-500 tw-transition-colors"></i>
                        <span class="tw-font-medium">Temas Representativos da TNU</span>
                    </a>
                </div>
            </section>
        </div>
        
        <!-- Call to Action Search -->
        <div class="tw-mt-8 tw-text-center">
            <a href="{{ route('searchpage') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-6 tw-py-3 tw-bg-brand-600 hover:tw-bg-brand-700 tw-text-white tw-font-medium tw-rounded-lg tw-transition-colors tw-shadow-sm hover:tw-shadow">
                <i class="fa fa-search"></i>
                <span>Ir para a Busca Geral</span>
            </a>
        </div>
    </div>


@endsection
