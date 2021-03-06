@extends('front.content_results')

@section('sumulas_total_text')
    @if (empty($output['sumula']['total']))
        <span id="trib-sum-num">Nenhum enunciado encontrado 
    @elseif ($output['sumula']['total'] == 1)
        <span class="text-primary font-w700" id="trib-sum-num">1</span> enunciado encontrado 
    @else
        <span class="text-primary font-w700" id="trib-sum-num">{{ $output['sumula']['total'] }}</span> enunciados encontrados 
    @endif
        no STJ para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
    </span>
@endsection

@section('sumulas_inner_table')
    @include('front.results.inners.stj_sum')
@endsection

@section('teses_total_text')
    @if (empty($output['tese']['total']))
        <span id="trib-rep-num">Nenhum tema de repetitivo encontrado 
    @elseif ($output['tese']['total'] == 1)
        <span class="text-primary font-w700" id="trib-rep-num">1</span> tema de repetitivo encontrado 
    @else
        <span class="text-primary font-w700" id="trib-rep-num">{{ $output['tese']['total'] }}</span> temas de repetitivos encontrados 
    @endif
        no STJ para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
    </span>
@endsection

@section('teses_inner_table')
  @include('front.results.inners.stj_rep')
@endsection