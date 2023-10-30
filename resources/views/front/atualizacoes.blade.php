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

            <select id="dateFilter" class="form-control" style="width: auto;" multiple>
                <option value="all">Todas as datas</option>
                <!-- As datas serão populadas aqui via jQuery -->
            </select>

        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <div class="block-content">
            <div class="tab-pane fade fade-up active show" id="tema-stf" role="tabpanel">
                @foreach ($tribunais as $tribunal => $log_types)
                    @php
                        $color = config('tes_constants.lista_tribunais.' . strtoupper($tribunal) . '.color');
                    @endphp
                    <div class="tribunal-header font-size-h4 font-w600 p-2 mb-4 border-left border-4x bg-body-light trib-texto-quantidade"
                        style="border-color: {{ $color }} !important;">
                        {{ config('tes_constants.lista_tribunais.' . strtoupper($tribunal) . '.name') }} -
                        {{ strtoupper($tribunal) }}
                    </div>

                    @if (!empty($log_types['news']))
                        <table class="table table-striped table-vcenter table-results"
                            style="border: 1px solid {{ $color }};">
                            <tbody>
                                <tr class="header-row">
                                    <td style="background: {{ $color }};">
                                        <h4 class="h5 mt-3 mb-2" style="color: #fff;">
                                            Novidades ({{ strtoupper($tribunal) }})
                                        </h4>
                                    </td>
                                </tr>
                                @foreach ($log_types['news'] as $log)
                                    <tr>
                                        <td>
                                            @if ($log->tipo == 'súmula')
                                                <h4 class="h5 mt-3 mb-2">
                                                    <a href="{{ $log->link }}" style="color: {{ $color }}">
                                                        {{ $log->original->titulo }} - {{ strtoupper($tribunal) }}</a>
                                                </h4>
                                            @elseif($log->tipo == 'tese')
                                                <h4 class="h5 mt-3 mb-2">
                                                    <a href="{{ $log->link }}" style="color: {{ $color }}">
                                                        Tema {{ $log->original->numero }} -
                                                        {{ strtoupper($tribunal) }}</a>
                                                </h4>
                                            @endif
                                            <p class="d-sm-block" style="font-weight: bold;">
                                                {{ $log->original->texto }}
                                            </p>
                                            <div style="display: flex; justify-content: space-between;">
                                                <span class="text-muted" style="font-size: 0.8em;"
                                                    data-date="{{ date('d/m/Y', strtotime($log->created_at)) }}">
                                                    {{ date('d/m/Y', strtotime($log->created_at)) }}
                                                </span>
                                                <span class="text-muted" style="font-size: 0.8em;">
                                                    Acesse&nbsp; <a href="{{ $log->link }}"
                                                        style="color: {{ $color }}"> aqui</a>.
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if (!empty($log_types['updates']))
                        <table class="table table-striped table-vcenter table-results"
                            style="border: 1px solid {{ $color }};">
                            <tbody>
                                <tr class="header-row">
                                    <td style="background: {{ $color }}">
                                        <h4 class="h5 mt-3 mb-2" style="color: #fff;">
                                            Alterações ({{ strtoupper($tribunal) }})
                                        </h4>
                                    </td>
                                </tr>
                                @foreach ($log_types['updates'] as $log)
                                    <tr>
                                        <td>
                                            <h4 class="h5 mt-3 mb-2">
                                                <a href="{{ $log->link }}" style="color: {{ $color }}">
                                                    @if ($log->tipo == 'súmula')
                                                        Súmula {{ $log->numero }}
                                                    @elseif($log->tipo == 'tese')
                                                        Tema {{ $log->numero }}
                                                    @endif
                                                    - {{ strtoupper($tribunal) }}
                                                </a>
                                            </h4>
                                            <span class="text-muted">
                                                Objeto da Atualização: {{ $log->col_altered }}
                                            </span>
                                            <p class="d-sm-block" style="font-weight: bold;">
                                                {{ $log->new_value }}
                                            </p>
                                            <div style="display: flex; justify-content: space-between;">
                                                <span class="text-muted" style="font-size: 0.8em;"
                                                    data-date="{{ date('d/m/Y', strtotime($log->updated_at)) }}">
                                                    {{ date('d/m/Y', strtotime($log->updated_at)) }}
                                                </span>

                                                <span class="text-muted" style="font-size: 0.8em;">
                                                    Acesse&nbsp; <a href="{{ $log->link }}"
                                                        style="color: {{ $color }}"> aqui</a>.
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    <hr>
                @endforeach
            </div>
        </div>
    </div>

    <!-- END Page Content -->

@endsection

@section('atualizacoesjs')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            let dates = [];

            // Coletar todas as datas da página
            $('span.text-muted[data-date]').each(function() {
                let currentDate = $(this).data('date');
                if (!dates.includes(currentDate)) {
                    dates.push(currentDate);
                }
            });

            dates.sort(function(a, b) {
                let partsA = a.split('/');
                let partsB = b.split('/');
                return new Date(partsB[2], partsB[1] - 1, partsB[0]) - new Date(partsA[2], partsA[1] - 1,
                    partsA[0]);
            });

            // Populando o dropdown com as datas
            dates.forEach(function(date) {
                $('#dateFilter').append('<option value="' + date + '">' + date + '</option>');
            });

            // Evento para filtrar as linhas baseado nas datas selecionadas
            $('#dateFilter').on('change', function() {
                let selectedDates = $(this).val();

                if (selectedDates.includes("all")) {
                    $('table.table-results tbody tr').show();
                    $('.tribunal-header').show();
                } else {
                    $('table.table-results tbody tr').not('.header-row').hide();

                    $('table.table-results tbody tr').each(function() {
                        let rowDate = $(this).find('span.text-muted[data-date]').data('date');
                        if (selectedDates.includes(rowDate)) {
                            $(this).show();
                        }
                    });

                    // Exibe os cabeçalhos se houver alguma linha visível após eles em cada tabela.
                    $('table.table-results').each(function() {
                        let $table = $(this);
                        let $tribunalHeader = $table.prevAll('.tribunal-header').first();
                        if ($table.find('tr:not(.header-row):visible').length > 0) {
                            $table.find('tr.header-row').show();
                            $tribunalHeader.show();
                        } else {
                            $table.find('tr.header-row').hide();
                            if ($table.find('tr:visible').length === 0) {
                                $tribunalHeader.hide();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
