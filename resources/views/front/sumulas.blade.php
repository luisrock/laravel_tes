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

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="content content-full pt-2 pb-0">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <div class="content" id="content-results">

        <!-- Results -->

        <div class="block-content tab-content overflow-hidden">
            <div class="block-content tab-content overflow-hidden">


                <div class="tab-pane fade fade-up active show" role="tabpanel">

                    <!-- Search Container -->
                    <div id="search-container" class="d-none mb-3 p-3 bg-white border rounded shadow-sm">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" id="table-search-input" 
                                placeholder="Pesquisar por número, texto{{ $tribunal == 'STJ' ? ', órgão julgador' : '' }}...">
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="button" id="clear-search-btn" style="display:none;">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted ml-1">Digite para filtrar instantaneamente.</small>
                    </div>
                    <!-- END Search Container -->

                    <div
                        class="d-flex justify-content-between align-items-center font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">
                        <div>
                            <code>Súmulas</code> - {{ $tribunal }}
                            (resultados: <code>{{ $count }}</code>)
                        </div>
                        <button class="btn btn-sm btn-alt-primary" id="toggle-search-btn">
                            <i class="fa fa-search mr-1"></i> Buscar
                        </button>
                    </div>

                    <table class="table table-striped table-vcenter table-results">

                        <tbody>
                            @foreach ($sumulas as $sum)
                                <tr>
                                    <td>
                                        <h4 class="h5 mt-3 mb-2">
                                            <a href="{{ route($sumula_route, ['sumula' => $sum->id]) }}">
                                                {{ $sum->titulo }}</a>
                                        </h4>
                                        <p class="d-sm-block" style="font-weight: bold;">
                                            {{ $sum->texto }}
                                        </p>



                                        @if (isset($sum->isCancelada) && $sum->isCancelada)
                                            <p class="text-muted" style="font-size: 0.9em;">
                                                SÚMULA CANCELADA
                                            </p>
                                        @endif

                                        <span class="text-muted"
                                            style="display: flex;justify-content: flex-end;font-size: 0.8em;">
                                            {{ $sum->tempo }}
                                        </span>



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
