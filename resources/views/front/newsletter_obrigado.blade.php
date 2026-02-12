@extends('front.base')

@section('page-title', 'Newsletter Obrigado')

@section('content')
    <div class="home-pilot-shell tw-pt-6" style="{{ $display_pdf }}">
        <section class="home-pilot-card tw-max-w-2xl tw-mx-auto tw-p-6 md:tw-p-8 tw-border tw-border-slate-300 tw-shadow-sm">
            <h2 class="tw-text-3xl tw-font-bold tw-text-brand-700 tw-mb-3">Obrigado!</h2>
            <p class="tw-text-slate-700 tw-leading-relaxed tw-font-semibold">
                Você receberá um email semanal contendo as atualizações em teses e súmulas dos principais tribunais, com
                curadoria feita pelo Juiz federal Mauro Lopes, criador do Teses e Súmulas.
            </p>
        </section>
    </div>


    <!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


@endsection
