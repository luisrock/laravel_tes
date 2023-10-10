@extends('front.base')

@section('page-title', 'Atualizações')

@section('content')

    <!-- Hero -->
    <div class="bg-body-light" style="{{ $display_pdf }}">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">
                    <a href="{{ url('/') }}">
                        Teses & Súmulas
                    </a>
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="badge badge-primary">Extensão para o Chrome</a>
                </span>
            </div>
            <p>
                Veja aqui as últimas atualizações de teses e súmulas trazidas para o site.
            </p>
            <h2>
                Últimas Atualizações do Site
            </h2>
        </div>
    </div>
    <!-- END Hero -->
    <div class="content" id="content-results">
        <div class="block-content">
            <div class="tab-pane fade fade-up active show" id="tema-stf" role="tabpanel">
                @foreach ($tribunais as $tribunal => $log_types)
                    <div
                        class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">
                        {{ config('tes_constants.lista_tribunais.' . strtoupper($tribunal) . '.name') }} -
                        {{ strtoupper($tribunal) }}
                    </div>

                    @if (!empty($log_types['news']))
                        <table class="table table-striped table-vcenter table-results" style="border: 1px solid #87a0dc;">
                            <tbody>
                                <tr>
                                    <td style="background: #87a0dc;">
                                        <h4 class="h5 mt-3 mb-2" style="color: #fff;">
                                            Novidades
                                        </h4>
                                    </td>
                                </tr>
                                @foreach ($log_types['news'] as $log)
                                    <tr>
                                        <td>
                                            @if ($log->tipo == 'súmula')
                                                <h4 class="h5 mt-3 mb-2">
                                                    <a href="{{ $log->link }}">
                                                        {{ $log->original->titulo }}</a>
                                                </h4>
                                                <p class="d-sm-block" style="font-weight: bold;">
                                                    {{ $log->original->texto }}
                                                </p>
                                            @elseif($log->tipo == 'tese')
                                                <h4 class="h5 mt-3 mb-2">
                                                    <a href="{{ $log->link }}">
                                                        Tema {{ $log->original->numero }}</a>
                                                </h4>
                                                <p class="d-sm-block" style="font-weight: bold;">
                                                    @if (!empty($log->original->tese))
                                                        {{ $log->original->tese }}
                                                    @elseif(!empty($log->original->texto))
                                                        {{ $log->original->texto }}
                                                    @elseif(!empty($log->original->tese_texto))
                                                        {{ $log->original->tese_texto }}
                                                    @endif
                                                </p>
                                            @endif
                                            <div style="display: flex; justify-content: space-between;">
                                                <span class="text-muted" style="font-size: 0.8em;">
                                                    {{ date('d/m/Y', strtotime($log->created_at)) }}
                                                </span>
                                                <span class="text-muted" style="font-size: 0.8em;">
                                                    Acesse&nbsp; <a href="{{ $log->link }}"> aqui</a>.
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if (!empty($log_types['updates']))
                        <table class="table table-striped table-vcenter table-results" style="border: 1px solid #87a0dc;">
                            <tbody>
                                <tr>
                                    <td style="background: #87a0dc;">
                                        <h4 class="h5 mt-3 mb-2" style="color: #fff;">
                                            Alterações
                                        </h4>
                                    </td>
                                </tr>
                                @foreach ($log_types['updates'] as $log)
                                    <tr>
                                        <td>
                                            <h4 class="h5 mt-3 mb-2">
                                                <a href="{{ $log->link }}">
                                                    @if ($log->tipo == 'súmula')
                                                        Súmula {{ $log->numero }}
                                                    @elseif($log->tipo == 'tese')
                                                        Tema {{ $log->numero }}
                                                    @endif
                                                </a>
                                            </h4>
                                            <span class="text-muted">
                                                Objeto da Atualização: {{ $log->col_altered }}
                                            </span>
                                            <p class="d-sm-block" style="font-weight: bold;">
                                                {{ $log->new_value }}
                                            </p>
                                            <div style="display: flex; justify-content: space-between;">
                                                <span class="text-muted" style="font-size: 0.8em;">
                                                    {{ date('d/m/Y', strtotime($log->updated_at)) }}
                                                </span>
                                                <span class="text-muted" style="font-size: 0.8em;">
                                                    Acesse&nbsp; <a href="{{ $log->link }}"> aqui</a>.
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

            </div>
            @endforeach


        </div>
    </div>


    <!-- END Page Content -->

@endsection
