@foreach ($output['sumula']['hits'] as $sum)
    <tr>
        <td>
            <h4 class="h5 mt-3 mb-2">
                @if (Str::contains($sum['trib_sum_titulo'], 'QUESTÃO DE ORDEM'))
                    <a href="{{ $sum['trib_sum_url'] }}" target="_blank">
                    @else
                        <a href="{{ url('/sumula') }}/tnu/{{ $sum['trib_sum_id'] }}">
                @endif
                {{ $sum['trib_sum_titulo'] }}
                </a>
            </h4>
            <p class="d-sm-block" style="font-weight: bold;">
                {{ $sum['trib_sum_texto'] }}
            </p>
            <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
                {{ $sum['trib_sum_dados'] }}</span>
            <span class="tes-clear tes-text-to-be-copied" style="display: none"
                data-spec="trim">{{ $sum['trib_sum_titulo'] }}. {{ $sum['trib_sum_texto'] }}
                {{ $sum['trib_sum_dados'] }}</span>
            <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text">
                <span>
                    <i class="fa fa-copy"></i>
                </span>
            </button>
        </td>
    </tr>
@endforeach
