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
