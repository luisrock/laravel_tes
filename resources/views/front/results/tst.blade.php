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
          no TST para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
      </span>
  @endsection

  @section('sumulas_inner_table')
      @foreach ($output['sumula']['hits'] as $sum)
      <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="javascript:void(0);"> {{ $sum['trib_sum_tipo'] }} nº {{ $sum['trib_sum_numero'] }}</a>
          </h4>
          <p class="d-sm-block text-muted">
              {{ $sum['trib_sum_titulo'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
              {{ $sum['trib_sum_texto'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
            Última publicação: {{ $sum['trib_sum_data'] }}
            (status: {{ $sum['trib_sum_situacao'] }})
          </span>
        </td>
      </tr>
      @endforeach
  @endsection

  @section('teses_total_text')
      @if (empty($output['orientacao_precedente']['total']))
          <span id="trib-rep-num">tese de orientação jurisprudencial ou de precedente normativo encontrada 
      @elseif ($output['orientacao_precedente']['total'] == 1)
          <span class="text-primary font-w700" id="trib-rep-num">1</span> tese de orientação jurisprudencial ou de precedente normativo encontrada 
      @else
          <span class="text-primary font-w700" id="trib-rep-num">{{ $output['orientacao_precedente']['total'] }}</span> teses de orientação jurisprudencial ou de precedente normativo encontradas 
      @endif
          no TST para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
      </span>
  @endsection

  @section('teses_inner_table')
      @foreach ($output['orientacao_precedente']['hits'] as $rep)
      <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="javascript:void(0);"> {{ $rep['trib_rep_tipo'] }} nº {{ $rep['trib_rep_numero'] }}</a>
          </h4>
          <p class="d-sm-block text-muted">
              {{ $rep['trib_rep_titulo'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
              {{ $rep['trib_rep_texto'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
            Última publicação: {{ $rep['trib_rep_data'] }}
            (status: {{ $rep['trib_rep_situacao'] }})
          </span>
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