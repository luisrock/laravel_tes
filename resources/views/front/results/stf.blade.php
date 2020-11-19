@extends('front.content_results')

@section('sumulas_total_text')
    @if (empty($output['sumula']['total']))
        <span id="trib-sum-num">Nenhum enunciado encontrado 
    @elseif ($output['sumula']['total'] == 1)
        <span class="text-primary font-w700" id="trib-sum-num">1</span> enunciado encontrado 
    @else
        <span class="text-primary font-w700" id="trib-sum-num">{{ $output['sumula']['total'] }}</span> enunciados encontrados 
    @endif
        no STF para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
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
        <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">Aprovada em {{ $sum['trib_sum_data'] }}</span>
      </td>
    </tr>
    @endforeach
@endsection

@section('teses_total_text')
    @if (empty($output['repercussao']['total']))
        <span id="trib-rep-num">Nenhuma tese de repercussão geral com mérito julgado encontrada 
    @elseif ($output['repercussao']['total'] == 1)
        <span class="text-primary font-w700" id="trib-rep-num">1</span> tese de repercussão geral com mérito julgado encontrada 
    @else
        <span class="text-primary font-w700" id="trib-rep-num">{{ $output['repercussao']['total'] }}</span> teses de repercussão geral com mérito julgado encontradas 
    @endif
        no STF para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
    </span>
@endsection

@section('teses_inner_table')
    @foreach ($output['repercussao']['hits'] as $rep)
    <tr>
      <td>
        <h4 class="h5 mt-3 mb-2">
          <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> {{ $rep['trib_rep_titulo'] }}</a>
        </h4>
        <p class="d-sm-block text-muted">
          TEMA: {{ $rep['trib_rep_tema'] }}
        </p>
        <p class="d-sm-block" style="font-weight: bold;">
          {{ $rep['trib_rep_tese'] }}
        </p>
        <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">{{ $rep['trib_rep_relator'] }}, aprovada em {{ $rep['trib_rep_data'] }}.</span>
      </td>
    </tr>
    @endforeach
@endsection