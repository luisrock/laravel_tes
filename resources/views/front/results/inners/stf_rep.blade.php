@foreach ($output['tese']['hits'] as $rep)
      <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> {{ $rep['trib_rep_titulo'] }}</a>
          </h4>
          <p class="d-sm-block text-muted">
            TEMA: {{ $rep['trib_rep_tema'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
            {{ $rep['trib_rep_tese'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">{{ $rep['trib_rep_relator'] }}, aprovada em {{ $rep['trib_rep_data'] }}.</span>
        </td>
      </tr>
@endforeach