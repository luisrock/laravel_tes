@extends('front.content_results')

@section('sumulas_total_text')
    @if (empty($output['sumula']['total']))
        <span id="trib-sum-num">Nenhum enunciado encontrado 
    @elseif ($output['sumula']['total'] == 1)
        <span class="text-primary font-w700" id="trib-sum-num">1</span> enunciado encontrado 
    @else
        <span class="text-primary font-w700" id="trib-sum-num">{{ $output['sumula']['total'] }}</span> enunciados encontrados 
    @endif
        na TNU para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
    </span>
@endsection

@section('sumulas_inner_table')
    @foreach ($output['sumula']['hits'] as $sum)
    <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="{{ $sum['trib_sum_url'] }}" target="_blank"> {{ $sum['trib_sum_titulo'] }}</a>
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
        <span id="trib-rep-num">Nenhum tema representativo encontrado 
    @elseif ($output['repetitivo']['total'] == 1)
        <span class="text-primary font-w700" id="trib-rep-num">1</span> tema representativo encontrado 
    @else
        <span class="text-primary font-w700" id="trib-rep-num">{{ $output['repetitivo']['total'] }}</span> temas representativos encontrados 
    @endif
        na TNU para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
    </span>
@endsection

@section('teses_inner_table')
    @foreach ($output['repetitivo']['hits'] as $rep)
      <tr>
        <td>
          <div style="display:flex; justify-content:space-between;"> 
            <h4 class="h5 mt-1 mb-3" style="flex: none;margin-right: 30px;">
              <a href="javascript:void(0);"> {{ $rep['trib_rep_titulo'] }}</a>
            </h4>
            <span class="text-muted mt-1 mb-3" style="font-size: 0.8em;">
            <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> {{ $rep['trib_rep_processo'] }}</a>
            </span>
          </div>
          <p class="d-sm-block text-muted">
            QUESTÃO: {{ $rep['trib_rep_tema'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
            {{ $rep['trib_rep_tese'] }}
          </p>
          @if (!empty($rep['trib_rep_obs'])) 
            <p class="d-sm-block text-muted">
              {{ $rep['trib_rep_obs'] }}
            </p>
          @endif
          <div class="text-muted" style="display: flex;justify-content: space-between;font-size: 0.8em;">
            <span style="margin-right: 30px;">
              {{ $rep['trib_rep_relator'] }}
            </span>
            <span>
              Situação: {{ $rep['trib_rep_situacao'] }} 
              (última atualização em {{ $rep['trib_rep_data'] }})
            </span>
          </div>
        </td>
      </tr>
    @endforeach
@endsection