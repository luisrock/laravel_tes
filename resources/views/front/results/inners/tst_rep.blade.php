@foreach ($output['tese']['hits'] as $rep)
      <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="{{ $rep['trib_rep_url'] }}"> {{ $rep['trib_rep_tipo'] }} nº {{ $rep['trib_rep_numero'] }}</a>
          </h4>
          <p class="d-sm-block text-muted">
              {{ $rep['trib_rep_tema'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
              {{ $rep['trib_rep_texto'] }}
          </p>
          <span class="tes-clear tes-text-to-be-copied" style="display: none" data-spec="trim">{{ $rep['trib_rep_tipo'] }} nº {{ $rep['trib_rep_numero'] }}. {{ $rep['trib_rep_tema'] }}. TEXTO: {{ $rep['trib_rep_texto'] }}</span>
          <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text">
            <span>
              <i class="fa fa-copy"></i>
            </span>
          </button>
        </td>
      </tr>
@endforeach