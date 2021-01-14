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
        </td>
      </tr>
@endforeach