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
                        <code>Súmulas</code> - {{ $tribunal }}
                        (resultados: <code>{{ $count }}</code>)
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
