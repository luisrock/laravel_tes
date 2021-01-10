@foreach ($output['tese']['hits'] as $rep)
      <tr>
        <td>
          <h4 class="h5 mt-3 mb-2">
            <a href="javascript:void(0);"> {{ $rep['trib_rep_tipo'] }} nº {{ $rep['trib_rep_numero'] }}</a>
          </h4>
          <p class="d-sm-block text-muted">
              {{ $rep['trib_rep_titulo'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
              {{ $rep['trib_rep_texto'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
            Última publicação: {{ $rep['trib_rep_data'] }}
            (status: {{ $rep['trib_rep_situacao'] }})
          </span>
        </td>
      </tr>
@endforeach