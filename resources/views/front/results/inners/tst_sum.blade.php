@foreach ($output['sumula']['hits'] as $sum)
      <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="javascript:void(0);"> {{ $sum['trib_sum_tipo'] }} nº {{ $sum['trib_sum_numero'] }}</a>
          </h4>
          <p class="d-sm-block text-muted">
              {{ $sum['trib_sum_titulo'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
              {{ $sum['trib_sum_texto'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
            Última publicação: {{ $sum['trib_sum_data'] }}
            (status: {{ $sum['trib_sum_situacao'] }})
          </span>
        </td>
      </tr>
@endforeach
