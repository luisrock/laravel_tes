@foreach ($output['tese']['hits'] as $rep)
    <tr>
        <td>
            <div style="display:flex; justify-content:space-between;">
                <h4 class="h5 mt-3 mb-2">
                    <a href="{{ url('/tese') }}/stj/{{ $rep['trib_rep_id'] }}"> Tema/Repetitivo
                        {{ $rep['trib_rep_numero'] }}</a>
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
                (última verificação em {{ $rep['trib_rep_data'] }})
            </span>
            <span class="tes-clear tes-text-to-be-copied" style="display: none">TEMA {{ $rep['trib_rep_numero'] }}
                ({{ $rep['trib_rep_orgao'] }}): {{ $rep['trib_rep_tema'] }} TESE: {{ $rep['trib_rep_tese'] }}
                SITUAÇÃO:
                {{ $rep['trib_rep_situacao'] }}</span>
            <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text">
                <span>
                    <i class="fa fa-copy"></i>
                </span>
            </button>

        </td>
    </tr>
@endforeach
