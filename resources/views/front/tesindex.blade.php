@extends('front.base')

@section('page-title', 'Súmulas de Tribunais')

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
                    <span class="text-muted"> | </span> Teses e Súmulas de Tribunais
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="badge badge-primary">Extensão para o Chrome</a>
                </span>
            </div>
            <p>
                Faça uma <a href="{{ route('searchpage') }}">pesquisa</a> ou veja as <a
                    href="{{ route('alltemaspage') }}">pesquisas prontas</a>.
            </p>
        </div>
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

    <div class="content" id="content-results">

        <div class="block-content tab-content overflow-hidden">
            <div class="block-content tab-content overflow-hidden">
                <h2>SÚMULAS</h2>
                <div class="tab-pane fade fade-up active show" role="tabpanel">
                    <h4 class="h5 mt-3 mb-2">
                        <a href="{{ route('stfallsumulaspage') }}">
                            &rArr; Súmulas do Supremo Tribunal Federal
                        </a>
                    </h4>
                    <h4 class="h5 mt-3 mb-2">
                        <a href="{{ route('stjallsumulaspage') }}">
                            &rArr; Súmulas do Superior Tribunal de Justiça
                        </a>
                    </h4>
                    <h4 class="h5 mt-3 mb-2">
                        <a href="{{ route('tstallsumulaspage') }}">
                            &rArr; Súmulas do Tribunal Superior do Trabalho
                        </a>
                    </h4>
                    <h4 class="h5 mt-3 mb-2">
                        <a href="{{ route('tnuallsumulaspage') }}">
                            &rArr; Súmulas da Turma Nacional de Uniformização dos JEF
                        </a>
                    </h4>
                </div>
            </div>
        </div>

        <div class="block-content tab-content overflow-hidden">
            <div class="block-content tab-content overflow-hidden">
                <h2>TESES</h2>
                <div class="tab-pane fade fade-up active show" role="tabpanel">
                    <h4 class="h5 mt-3 mb-2">
                        <a href="{{ route('stfalltesespage') }}">
                            &rArr; Teses Vinculantes do Supremo Tribunal Federal
                        </a>
                    </h4>
                    <h4 class="h5 mt-3 mb-2">
                        <a href="{{ route('stjalltesespage') }}">
                            &rArr; Teses Vinculantes do Superior Tribunal de Justiça
                        </a>
                    </h4>
                </div>
            </div>
        </div>

    </div>


@endsection
