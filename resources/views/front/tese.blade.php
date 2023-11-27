@extends('front.base')

@section('page-title', $label)

@section('content')

    <!-- Page Content -->

    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">
                    <a href="{{ url('/') }}">
                        Teses & Súmulas
                    </a>
                    <span class="text-muted"> | </span> {{ $label }}
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="badge badge-primary">Extensão para o Chrome</a>
                </span>
            </div>
            <p>
                Faça uma <a href="{{ route('searchpage') }}">pesquisa</a> ou veja as <a
                    href="{{ route('alltemaspage') }}">pesquisas prontas</a>.
                @if ($admin)
                    <br><a href="{{ route('admin') }}">Admin</a>
                @endif
            </p>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content" id="content-results">

        <!-- Results -->

        <div class="block-content tab-content overflow-hidden">
            <div class="block-content tab-content overflow-hidden">


                <div class="tab-pane fade fade-up active show" role="tabpanel">

                    <div
                        class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">
                        {{ $tribunal_nome_completo }} - {{ $tribunal }}
                    </div>

                    <table class="table table-striped table-vcenter table-results table-sumula"
                        style="border: 2px solid #5c80d1;">

                        <tbody>

                            <tr>
                                <td>
                                    <h4 class="h5 mt-3 mb-2" style="color:#6d8cd5;">
                                        @if (!empty($tese->link))
                                            <a href="{{ $tese->link }}" target="_blank">
                                                {{ $tese->titulo }}
                                            </a>
                                        @else
                                            {{ $tese->titulo }}
                                        @endif
                                    </h4>
                                    @if (!empty($tese->questao))
                                        <p class="d-sm-block" style="">
                                            {{ $tese->questao }}
                                        </p>
                                    @endif
                                    @if (!empty($tese->texto))
                                        <p class="d-sm-block" style="font-weight: bold;">
                                            {{ $tese->texto }}
                                        </p>
                                    @endif
                                    @if (!empty($tese->text_muted))
                                        <span class="text-muted"
                                            style="display: flex;justify-content: flex-end;font-size: 0.8em;">
                                            {{ $tese->text_muted }}
                                        </span>
                                    @endif
                                    @if (!empty($tese->to_be_copied))
                                        <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text">
                                            <span>
                                                <i class="fa fa-copy"></i>
                                            </span>
                                        </button>
                                        <span class="tes-clear tes-text-to-be-copied" style="display: none"
                                            data-spec="trim">{{ $tese->to_be_copied }}
                                        </span>
                                    @endif

                                </td>
                            </tr>

                        </tbody>

                    </table>

                    @if (!empty($tese->ementa_texto))
                        {{-- STF --}}
                        <h4 class="h5 mt-3 mb-2">
                            Ementa
                        </h4>
                        <p class="d-sm-block" style="">
                            {{ $tese->ementa_texto }}
                            <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
                                {{ $tese->relator }}, {{ $tese->acordao }}.
                            </span>
                        </p>
                    @endif

                    @if (!empty($tese->indexacao))
                        {{-- STF --}}
                        <h4 class="h5 mt-3 mb-2">
                            Indexação
                        </h4>
                        <p class="d-sm-block" style="">
                            {{ $tese->indexacao }}
                        </p>
                    @endif

                    @if (!empty($tese->ramos))
                        {{-- STJ --}}
                        <h4 class="h5 mt-3 mb-2">
                            Assuntos
                        </h4>
                        <p class="d-sm-block" style="">
                            {{ $tese->ramos }}
                        </p>
                    @endif

                    @if (!empty($tese->link))
                        <p class="d-sm-block" style="">
                            Consulte a fonte
                            <a href="{{ $tese->link }}" target="_blank">
                                aqui</a>
                        </p>
                    @endif

                </div>

            </div>

            <!-- END Results -->

            <div class="block-content block-content-full block-content-sm bg-body-light">
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <a href="{{ route($alltesesroute) }}">
                            <i class="fa fa-arrow-left mr-1"></i> Teses Vinculantes do {{ $tribunal_nome_completo }}
                        </a>
                    </div>
                </div>

            </div>


        @endsection
