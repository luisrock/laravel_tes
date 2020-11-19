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
    @foreach ($output['sumula']['hits'] as $sum)
    <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="javascript:void(0);"> {{ $sum['trib_sum_titulo'] }}</a>
          </h4>
          <p class="d-sm-block" style="font-weight: bold;">
            {{ $sum['trib_sum_texto'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;"> {{ $sum['trib_sum_dados'] }}</span>
        </td>
      </tr>
    @endforeach
@endsection

@section('teses_total_text')
    @if (empty($output['repetitivo']['total']))
        <span id="trib-rep-num">Nenhum tema de repetitivo encontrado 
    @elseif ($output['repetitivo']['total'] == 1)
        <span class="text-primary font-w700" id="trib-rep-num">1</span> tema de repetitivo encontrado 
    @else
        <span class="text-primary font-w700" id="trib-rep-num">{{ $output['repetitivo']['total'] }}</span> temas de repetitivos encontrados 
    @endif
        no STJ para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
    </span>
@endsection

@section('teses_inner_table')
    @foreach ($output['repetitivo']['hits'] as $rep)
      <tr>
        <td>
          <div style="display:flex; justify-content:space-between;"> 
            <h4 class="h5 mt-3 mb-2">
              <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> Tema/Repetitivo {{ $rep['trib_rep_numero'] }}</a>
            </h4>
            <span class="text-muted mt-3 mb-2">
              {{ $rep['trib_rep_orgao'] }}
            </span>
          </div>
          <p class="d-sm-block text-muted">
            QUESTÃO: {{ $rep['trib_rep_tema'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
            {{ $rep['trib_rep_tese'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
            Situação: {{ $rep['trib_rep_situacao'] }} 
            (última atualização em {{ $rep['trib_rep_data'] }})</span>
        </td>
      </tr>
    @endforeach
@endsection