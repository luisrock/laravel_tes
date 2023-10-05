@foreach ($output['tese']['hits'] as $rep)
    <tr>
        <td>
            <h4 class="h5 mt-3 mb-2">
                <a href="{{ url('/tese') }}/stf/{{ $rep['trib_rep_id'] }}"> {{ $rep['trib_rep_titulo'] }}</a>
            </h4>
            <p class="d-sm-block text-muted">
                TEMA: {{ $rep['trib_rep_tema'] }}
            </p>
            <p class="d-sm-block" style="font-weight: bold;">
                {{ $rep['trib_rep_tese'] }}
            </p>
            <span class="text-muted"
                style="display: flex;justify-content: flex-end;font-size: 0.8em;">{{ $rep['trib_rep_relator'] }},
                aprovada em {{ $rep['trib_rep_data'] }}.</span>
            <span class="tes-clear tes-text-to-be-copied" style="display: none">TEMA: {{ $rep['trib_rep_tema'] }} TESE:
                {{ $rep['trib_rep_tese'] }} {{ $rep['trib_rep_titulo'] }}, {{ $rep['trib_rep_relator'] }}, aprovada em
                {{ $rep['trib_rep_data'] }}.</span>
            <button class="btn btn-rounded btn-outline-primary btn-sm mr-1 mb-3 btn-copy-text">
                <span>
                    <i class="fa fa-copy"></i>
                </span>
            </button>
        </td>
    </tr>
@endforeach
