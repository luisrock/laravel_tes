@extends('front.content_results')

@section('sumulas_total_text')
    @if (empty($output['sumula']['total']))
        <span id="trib-sum-num">Nenhum enunciado encontrado 
    @elseif ($output['sumula']['total'] == 1)
        <span class="text-primary font-w700" id="trib-sum-num">1</span> enunciado encontrado 
    @else
        <span class="text-primary font-w700" id="trib-sum-num">{{ $output['sumula']['total'] }}</span> enunciados encontrados 
    @endif
        em CEJ/JORNADAS para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
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
        @if(!empty($sum['trib_sum_notas']))
          <p class="d-sm-block text-muted">
            NOTAS: {{ $sum['trib_sum_notas'] }}
          </p>
        @endif
        <div class="text-muted" style="display: flex;justify-content: space-between;font-size: 0.8em;">
          <span style="margin-right: 30px;">
            {{ $sum['trib_sum_legis'] }}
          </span>
          <span>
            {{ $sum['trib_sum_jornada'] }}
          </span>
        </div>
      </td>
    </tr>
    @endforeach
@endsection