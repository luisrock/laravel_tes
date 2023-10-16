@extends('front.base')

@section('page-title', 'Newsletter Obrigado')

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
        </div>
    </div>
    <!-- END Hero -->


    <!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


    <!-- Search -->
    <div class="content" style="max-width: 670px;border: 1px solid grey;margin-top: 50px;">
        <h2>Obrigado!</h2>
        <p><strong>Você receberá um email semanal contendo as atualizações em teses e súmulas dos principais tribunais, com
                curadoria
                feita pelo Juiz federal Mauro Lopes, criador do Teses e Súmulas.</strong></p>
    </div>

    <!-- END Page Content -->

@endsection
