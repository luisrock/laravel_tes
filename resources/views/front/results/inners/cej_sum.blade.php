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