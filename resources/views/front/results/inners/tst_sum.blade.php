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
          <span class="tes-clear tes-text-to-be-copied" style="display: none" data-spec="trim">{{ $sum['trib_sum_tipo'] }} nº {{ $sum['trib_sum_numero'] }}. {{ $sum['trib_sum_tema'] }}. TEXTO: {{ $sum['trib_sum_texto'] }}</span>
          <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text">
            <span>
              <i class="fa fa-copy"></i>
            </span>
          </button>
        </td>
      </tr>
@endforeach
