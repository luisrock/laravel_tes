@foreach ($output['sumula']['hits'] as $sum)
      <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="{{ $sum['trib_sum_url'] }}"> {{ $sum['trib_sum_tipo'] }} nº {{ $sum['trib_sum_numero'] }}</a>
          </h4>
          <p class="d-sm-block text-muted">
              {{ $sum['trib_sum_tema'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
              {{ $sum['trib_sum_texto'] }}
          </p>
        </td>
      </tr>
@endforeach
