@extends('front.base')

@section('page-title', 'Pesquisa')

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
            Pesquisa Simplificada de Súmulas e Teses de Repetitivos e de Repercussão Geral
            feita na base de dados de tribunais superiores
            e outros órgãos relevantes, com geração opcional de PDF contendo os resultados.
            Prepare seu estudo|aula|decisão|petição|parecer etc.
        </p>
    </div>
</div>
<!-- END Hero -->


<!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


<!-- Search -->
<div class="content" style="{{ $display_pdf }}">
    <form method="GET" id="trib-form">

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="input-group">
            <input type="text" class="form-control" name="q" value="{{ $keyword ?? '' }}" placeholder="Buscar..."
                required>
            <!-- <input type="hidden" name="keylabel"> -->
            <div class="input-group-append">
                <span class="input-group-text" style="cursor:pointer;"
                    onclick="document.getElementById('trib-form').submit();">
                    <i class="fa fa-fw fa-search"></i>
                </span>
            </div>
        </div>

        <div id="radios-tribunais">

            @foreach ($lista_tribunais as $t => $arr)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tribunal" id="{{ strtolower($t) }}" value="{{ $t }}"
                    @if ( !empty($tribunal) && strtolower($tribunal)===strtolower($t) ) checked @endif
                    <?php //echo ($tribunal === $t) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="{{ strtolower($t) }}">
                    {{ $t }}
                </label>
            </div>
            @endforeach

        </div>


        <div class="input-group">
            <input class="btn btn-sm btn-primary" id="btn-send-trib-form" type="submit" value="pesquisar">
        </div>

        <div class="spinner-border text-primary" id="spinning" role="status" style="display:none;">
            <!--                         <span class="sr-only">Buscando...</span> -->
        </div>
        <div id="loading-text" style="display:none;">
            Acessando a base do órgão...
        </div>

    </form>
</div>
<!-- END Search -->

@yield('content_results')

<!-- END Page Content -->

@endsection