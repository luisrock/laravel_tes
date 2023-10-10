@extends('front.base')

@section('page-title', 'Temas')

@section('content')

    @php
        $admin = false;
    @endphp
    @auth
        @php
            $admin = in_array(Auth::user()->email, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com']);
        @endphp
    @endauth

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
                Pesquisa Pronta de Súmulas, Enunciados e Teses de Repercussão Geral e Repetitivos
                feita na base de dados de tribunais superiores
                e outros órgãos relevantes. Escolha o seu tema e comece a estudar!
            </p>
            <h2>
                Pesquisas Prontas
            </h2>
            <p>
                (todos os tribunais)
            </p>
        </div>
    </div>
    <!-- END Hero -->


    <!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


    <!-- Temas -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header">
                <h3 class="block-title">Temas</h3>
                @if ($admin)
                    <h6 class="block-title">{{ $perc_total_concepts }}</h6>
                    <a href="{{ route('admin') }}">Admin</a>
                @endif
                <div class="block-options">
                    <div class="block-options-item">
                        <!-- <code>.table</code> -->
                    </div>
                </div>
            </div>
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-vcenter table-bordered">
                        <!-- <thead>
                                <tr>
                                    <th style="width: 33%;">Tema</th>
                                    <th style="width: 33%;">Tema</th>
                                    <th style="width: 33%;">Tema</th>
                                </tr>
                            </thead> -->
                        <tbody>
                            <tr>
                                @foreach ($temas as $k => $t)
                                    @php
                                        $style = $admin && $t->concept && $t->concept_validated_at ? 'background-color: #c3d1c3;' : '';
                                    @endphp
                                    <td class="font-w600 font-size-sm" style="{{ $style }}">
                                        <a
                                            href="{{ route('temapage') }}/{{ $t->slug }}">{{ $t->label ?? str_replace('"', '', $t->keyword) }}</a>
                                    </td>
                                    @if (is_int(($k + 1) / 3))
                            </tr>
                            <tr>
                                @endif
                                @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
    <!-- END Temas -->


    <!-- END Page Content -->

@endsection
