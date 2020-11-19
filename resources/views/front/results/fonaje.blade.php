@extends('front.content_results')

@section('sumulas_total_text')
    @if (empty($output['sumula']['total']))
        <span id="trib-sum-num">Nenhum enunciado encontrado 
    @elseif ($output['sumula']['total'] == 1)
        <span class="text-primary font-w700" id="trib-sum-num">1</span> enunciado encontrado 
    @else
        <span class="text-primary font-w700" id="trib-sum-num">{{ $output['sumula']['total'] }}</span> enunciados encontrados 
    @endif
        no FONAJE para <mark class="text-danger trib-keyword">{{ $keyword }}</mark>
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