@extends('front.base')

@section('page-title', $tema)

@yield('stj')

@section('content')

<!-- Page Content -->

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
            Tema: {{ $tema }}. <br>Faça outra pesquisa <a href="{{ url('/') }}">aqui</a>.
        </p>
    </div>
</div>
<!-- END Hero -->

<div class="content" id="content-results">

    <!-- Results -->
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-block" data-toggle="tabs" role="tablist" style="{{ $display_pdf }}">

            @foreach($output as $out => $put)
            @php 
            $class_link = ($loop->first) ? "nav-link active" : "nav-link"; 
            @endphp 
            <li class="nav-item" id="nav-{{$out}}">
                <a class="{{$class_link}}" href="#tema-{{$out}}">{{ strtoupper($out) }}</a>
            </li>
            @endforeach

        </ul>
        
        <div class="block-content tab-content overflow-hidden">


            @foreach($output as $out => $put)
            @php
            $class_pane = ($loop->first) ? "tab-pane fade fade-up active show" : "tab-pane fade fade-up"; 
            @endphp
            <div class="{{$class_pane}}" id="tema-{{$out}}" role="tabpanel">
                <div class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">
                    <code>{{ $tema }}</code> - {{ strtoupper($out) }} 
                    (resultados: <code>{{($output[$out]['total_count']) }}</code>)
                </div>
                <table class="table table-striped table-vcenter">
                    <tbody>
                        @includeif('front.results.inners.' . strtolower($out) . '_sum', ['output' => $output[$out]])
                        @includeif('front.results.inners.' . strtolower($out) . '_rep', ['output' => $output[$out]])
                    </tbody>
                </table>
            </div>
            @endforeach
                
        </div>
    </div>
    <!-- END Results -->

</div>


@endsection