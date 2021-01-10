@extends('front.content_results')

@if(is_array($output))

    @section('sumulas_total_text')
        @if (empty($output['sumula']['total']))
            <span id="trib-sum-num">Nenhum enunciado encontrado 
        @elseif ($output['sumula']['total'] == 1)
            <span class="text-primary font-w700" id="trib-sum-num">1</span> enunciado encontrado 
        @else
            <span class="text-primary font-w700" id="trib-sum-num">{{ $output['sumula']['total'] }}</span> enunciados encontrados 
        @endif
            no TCU para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
        </span>
    @endsection

    @section('sumulas_inner_table')
        @include('front.results.inners.tcu_sum')
    @endsection

    @section('teses_total_text')
        @if (empty($output['tese']['total']))
            <span id="trib-rep-num">Nenhuma enunciado paradigmático encontrado 
        @elseif ($output['tese']['total'] == 1)
            <span class="text-primary font-w700" id="trib-rep-num">1</span> enunciado paradigmático encontrado 
        @else
            <span class="text-primary font-w700" id="trib-rep-num">{{ $output['tese']['total'] }}</span> enunciados paradigmáticos encontrados 
        @endif
            no TCU para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
        </span>
    @endsection

    @section('teses_inner_table')
        @include('front.results.inners.tcu_rep')
    @endsection

@elseif(is_string($output))

    @section('sumulas_total_text')
        <span id="trib-sum-num" style="color:red">
        {{$output}}
        </span>
    @endsection

    @section('teses_total_text')
        <span id="trib-rep-num" style="color:red">
        {{$output}}
        </span>
    @endsection

@endif