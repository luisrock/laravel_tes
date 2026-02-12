@extends('front.base')

@section('page-title', $label)

@section('content')

    <!-- Page Content -->

    <div class="home-pilot-shell tw-pt-4">
        <section class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-2">
            <h1 class="home-pilot-title tw-m-0">{{ $label }}</h1>
            <p class="home-pilot-subtitle tw-m-0">
                Faça uma <a href="{{ route('searchpage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">pesquisa</a> ou veja as
                <a href="{{ route('alltemaspage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">pesquisas prontas</a>.
                @if ($admin)
                    <br><a href="{{ route('admin') }}" class="tw-text-brand-700 hover:tw-text-brand-800">Admin</a>
                @endif
            </p>
        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="home-pilot-shell tw-pt-2 tw-pb-0">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <div class="home-pilot-shell tw-pt-2" id="content-results">

        <!-- Results -->

        <div class="home-pilot-card tw-p-5 md:tw-p-6">
            <div>


                <div class="tab-pane fade fade-up active show" role="tabpanel">

                    <!-- Search Container -->
                    <div id="search-container" class="tw-hidden tw-mb-3 tw-p-3 tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-shadow-sm">
                        <div class="tw-flex tw-items-stretch tw-gap-2">
                            <input type="text" class="home-pilot-input" id="table-search-input" 
                                placeholder="Pesquisar por tema, número, texto, {{ $tribunal == 'STF' ? 'relator' : 'órgão julgador' }}...">
                            <div>
                                <button class="tw-inline-flex tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-text-slate-600 hover:tw-bg-slate-100" type="button" id="clear-search-btn" style="display:none;">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small class="tw-text-slate-500 tw-ml-1">Digite para filtrar instantaneamente.</small>
                    </div>
                    <!-- END Search Container -->

                    <div class="home-results-count trib-texto-quantidade tw-flex tw-justify-between tw-items-center tw-gap-2">
                        <div>
                            <code>Teses</code> - {{ $tribunal }}
                            (resultados: <code>{{ $count }}</code>)
                        </div>
                        <button class="home-pilot-btn tw-py-2 tw-px-3 tw-text-sm" id="toggle-search-btn">
                            <i class="fa fa-search tw-mr-1"></i> Buscar
                        </button>
                    </div>

                    <table class="home-results-table table-results">

                        <tbody>
                            @foreach ($teses as $tes)
                                <tr>
                                    <td>
                                        @if (!$tes->isCancelada)
                                            <h4 class="tw-text-lg tw-font-semibold tw-mt-0 tw-mb-2">
                                                <a href="{{ route($tese_route, ['tese' => $tes->id]) }}">
                                                    TEMA {{ $tes->numero }}
                                                </a>
                                            </h4>
                                        @else
                                            <h4 class="tw-text-lg tw-font-semibold tw-mt-0 tw-mb-2 tw-text-slate-500">
                                                TEMA {{ $tes->numero }}
                                            </h4>
                                        @endif

                                        <p class="tw-text-slate-500">
                                            {{ $tes->tema_pure_text }}
                                        </p>
                                        <p class="tw-font-semibold">
                                            {{ $tes->tese_texto }}
                                        </p>
                                        @if ($tribunal == 'STF')
                                            <span class="tw-text-sm tw-flex tw-justify-end {{ $tes->isCancelada ? 'tw-text-red-600' : 'tw-text-slate-500' }}">
                                                {{ $tes->relator }}, {{ $tes->acordao }} ({{ $tes->situacao }}).
                                                {{ $tes->tempo }} </span>
                                            @elseif($tribunal == 'STJ')
                                                <span class="tw-text-sm tw-flex tw-justify-end {{ $tes->isCancelada ? 'tw-text-red-600' : 'tw-text-slate-500' }}">
                                                    {{ $tes->orgao }}. Situação: {{ $tes->situacao }}.
                                                    {{ $tes->tempo }} </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <!-- END Results -->

    </div>


@endsection

@section('scripts')
    <script src="{{ asset('assets/js/tes_search_filter.js') }}"></script>
@endsection
