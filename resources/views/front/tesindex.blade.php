@extends('front.base')

@section('page-title', 'Súmulas de Tribunais')

@section('content')

    <!-- Page Content -->

    <div class="home-pilot-shell tw-pt-4">
        <section class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-2">
            <h1 class="home-pilot-title tw-m-0">Índice de Súmulas e Teses</h1>
            <p class="home-pilot-subtitle tw-m-0">
                Acesse diretamente as coleções por tribunal com navegação rápida e objetiva.
            </p>
        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    <div class="content content-full pt-2 pb-0">
        <x-breadcrumb :items="[
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => null]
        ]" />
    </div>
    <!-- END Breadcrumb -->

    <div class="home-pilot-shell tw-pt-2" id="content-results">
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-4">
            <section class="home-pilot-card tw-p-5 md:tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-mb-4">Súmulas</h2>
                <div class="tw-space-y-2.5 tw-text-sm">
                    <a href="{{ route('stfallsumulaspage') }}" class="tw-block tw-text-brand-700 hover:tw-text-brand-800">→ Súmulas do Supremo Tribunal Federal</a>
                    <a href="{{ route('stjallsumulaspage') }}" class="tw-block tw-text-brand-700 hover:tw-text-brand-800">→ Súmulas do Superior Tribunal de Justiça</a>
                    <a href="{{ route('tstallsumulaspage') }}" class="tw-block tw-text-brand-700 hover:tw-text-brand-800">→ Súmulas do Tribunal Superior do Trabalho</a>
                    <a href="{{ route('tnuallsumulaspage') }}" class="tw-block tw-text-brand-700 hover:tw-text-brand-800">→ Súmulas da Turma Nacional de Uniformização dos JEF</a>
                </div>
            </section>

            <section class="home-pilot-card tw-p-5 md:tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-mb-4">Teses</h2>
                <div class="tw-space-y-2.5 tw-text-sm">
                    <a href="{{ route('stfalltesespage') }}" class="tw-block tw-text-brand-700 hover:tw-text-brand-800">→ Teses Vinculantes do Supremo Tribunal Federal</a>
                    <a href="{{ route('stjalltesespage') }}" class="tw-block tw-text-brand-700 hover:tw-text-brand-800">→ Teses Vinculantes do Superior Tribunal de Justiça</a>
                </div>
            </section>
        </div>
    </div>


@endsection
