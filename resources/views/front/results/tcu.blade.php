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
        @foreach ($output['sumula']['hits'] as $sum)
        @if(!empty($sum['trib_sum_vigente']) && $sum['trib_sum_vigente'] == 'false')
        @continue
        @endif     
        <tr>
        <td>
            <h4 class="h5 mt-3 mb-2">
            <a href="{{ $sum['trib_sum_url'] }}" target="_blank"> Súmula {{ $sum['trib_sum_numero'] }}</a>
            </h4>
            <p class="d-sm-block" style="font-weight: bold;">
                {!! $sum['trib_sum_texto'] !!}
            </p>
            <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">Aprovada em {{ $sum['trib_sum_data'] }}</span>
        </td>
        @endforeach
    @endsection

    @section('teses_total_text')
        @if (empty($output['resposta-consulta']['total']))
            <span id="trib-rep-num">Nenhuma enunciado paradigmático encontrado 
        @elseif ($output['resposta-consulta']['total'] == 1)
            <span class="text-primary font-w700" id="trib-rep-num">1</span> enunciado paradigmático encontrado 
        @else
            <span class="text-primary font-w700" id="trib-rep-num">{{ $output['resposta-consulta']['total'] }}</span> enunciados paradigmáticos encontrados 
        @endif
            no TCU para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
        </span>
    @endsection

    @section('teses_inner_table')
        @foreach ($output['resposta-consulta']['hits'] as $rep)
        <tr>
        <td>
            <h4 class="h5 mt-3 mb-2">
            <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> Acórdão  {{ $rep['trib_rep_acordao'] }}/{{ $rep['trib_rep_ano'] }} (resposta a consulta) - {{ $rep['trib_rep_orgao'] }}</a>
            </h4>
            <p class="d-sm-block" style="font-weight: bold;">
                {!! $rep['trib_rep_texto'] !!}
            </p>
            <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">{{ $rep['trib_rep_funcao'] }}: {{ $rep['trib_rep_autor'] }} | Data da sessão: {{ $rep['trib_rep_data'] }}.</span>
        </td>
        </tr>
        @endforeach
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