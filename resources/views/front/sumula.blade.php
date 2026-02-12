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


                <div role="tabpanel">

                    <div class="home-results-count trib-texto-quantidade">
                        {{ $tribunal_nome_completo }} - {{ $tribunal }}
                    </div>

                    <table class="home-results-table table-results table-sumula">

                        <tbody>

                            <tr>
                                <td>
                                    <h4 class="tw-text-lg tw-font-semibold tw-mt-0 tw-mb-2 tw-text-brand-700">
                                        @if (!empty($sumula->link))
                                            <a href="{{ $sumula->link }}" target="_blank">
                                                {{ $sumula->titulo }}
                                            </a>
                                        @else
                                            {{ $sumula->titulo }}
                                        @endif
                                    </h4>
                                    <p class="tw-font-semibold">
                                        {{ $sumula->texto }}
                                    </p>

                                    @if (!empty($sumula->obs))
                                        <p class="tw-text-slate-500 tw-text-sm">
                                            {{ $sumula->obs }}
                                        </p>
                                    @endif

                                    @if (!empty($sumula->isCancelada) && $sumula->isCancelada)
                                        <p class="tw-text-red-700 tw-text-sm tw-font-medium">
                                            SÚMULA CANCELADA
                                        </p>
                                    @endif

                                    <span class="tw-text-slate-500 tw-text-sm tw-flex tw-justify-end">
                                        {{ $sumula->tempo }}
                                    </span>

                                    @if (empty($sumula->isCancelada))
                                        <button class="btn-copy-text tw-mt-2">
                                            <span>
                                                <i class="fa fa-copy"></i>
                                            </span>
                                        </button>
                                        <span class="tes-clear tes-text-to-be-copied" style="display: none"
                                            data-spec="trim">{{ $sumula->to_be_copied }}
                                        </span>
                                    @endif

                                </td>
                            </tr>

                        </tbody>

                    </table>

                    <div role="tabpanel">
                        <table class="home-results-table table-results">
                            <tbody>
                                @if (!empty($sumula->legis))
                                    <tr>
                                        <td>
                                            <h4 class="tw-text-base tw-font-semibold tw-mt-0 tw-mb-2">
                                                Referência Legislativa
                                            </h4>
                                            <p>
                                                {{ $sumula->legis }}
                                            </p>
                                        </td>
                                    </tr>
                                @endif
                                @if (!empty($sumula->precedentes))
                                    <tr>
                                        <td>
                                            <h4 class="tw-text-base tw-font-semibold tw-mt-0 tw-mb-2">
                                                Precedentes
                                            </h4>
                                            <p>
                                                {{ $sumula->precedentes }}
                                            </p>
                                        </td>
                                    </tr>
                                @endif
                                @if (!empty($sumula->link))
                                    <tr>
                                        <td>
                                            <p>
                                                Consulte a súmula no site do tribunal:
                                                <a href="{{ $sumula->link }}" target="_blank" class="tw-text-brand-700 hover:tw-text-brand-800">
                                                    {{ $sumula->link }}
                                                </a>
                                            </p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>


        </div>

        <!-- END Results -->

        <div class="tw-mt-5 tw-pt-5 tw-border-t tw-border-slate-200 tw-text-center">
            <a href="{{ route($allsumulasroute) }}" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-brand-700 hover:tw-text-brand-800 tw-font-medium">
                <i class="fa fa-arrow-left"></i> Súmulas do {{ $tribunal_nome_completo }}
            </a>
        </div>


    @endsection
