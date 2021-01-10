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