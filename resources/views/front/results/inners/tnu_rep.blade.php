@foreach ($output['tese']['hits'] as $rep)
      <tr>
        <td>
          <div style="display:flex; justify-content:space-between;"> 
            <h4 class="h5 mt-1 mb-3" style="flex: none;margin-right: 30px;">
              <a href="javascript:void(0);"> {{ $rep['trib_rep_titulo'] }}</a>
            </h4>
            <span class="text-muted mt-1 mb-3" style="font-size: 0.8em;">
            <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> {{ $rep['trib_rep_processo'] }}</a>
            </span>
          </div>
          <p class="d-sm-block text-muted">
            QUESTÃO: {{ $rep['trib_rep_tema'] }}
          </p>
          <p class="d-sm-block" style="font-weight: bold;">
            {{ $rep['trib_rep_tese'] }}
          </p>
          @if (!empty($rep['trib_rep_obs'])) 
            <p class="d-sm-block text-muted">
              {{ $rep['trib_rep_obs'] }}
            </p>
          @endif
          <div class="text-muted" style="display: flex;justify-content: space-between;font-size: 0.8em;">
            <span style="margin-right: 30px;">
              {{ $rep['trib_rep_relator'] }}
            </span>
            <span>
              Situação: {{ $rep['trib_rep_situacao'] }} 
              (última atualização em {{ $rep['trib_rep_data'] }})
            </span>
          </div>
        </td>
      </tr>
@endforeach