@foreach ($output['tese']['hits'] as $rep)
      <tr>
        <td>
          <div style="display:flex; justify-content:space-between;"> 
            <h4 class="h5 mt-3 mb-2">
              <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> Tema/Repetitivo {{ $rep['trib_rep_numero'] }}</a>
            </h4>
            <span class="text-muted mt-3 mb-2">
              {{ $rep['trib_rep_orgao'] }}
            </span>
          </div>
          <p class="d-sm-block text-muted">
            QUESTÃO: {{ $rep['trib_rep_tema'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
            {{ $rep['trib_rep_tese'] }}
          </p>
          <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">
            Situação: {{ $rep['trib_rep_situacao'] }} 
            (última verificação em {{ $rep['trib_rep_data'] }})</span>
        </td>
      </tr>
@endforeach