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

        <span class="tes-clear tes-text-to-be-copied" style="display: none" data-spec="trim">{{ $sum['trib_sum_titulo'] }}. {{ $sum['trib_sum_texto'] }} @if(!empty($sum['trib_sum_notas']))NOTAS: {{$sum['trib_sum_notas']}}@endif {{ $sum['trib_sum_legis'] }} {{ $sum['trib_sum_jornada'] }}</span>
            <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text">
            <span>
              <i class="fa fa-copy"></i>
            </span>
          </button>
      </td>
    </tr>
@endforeach