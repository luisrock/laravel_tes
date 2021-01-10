@foreach ($output['tese']['hits'] as $rep)
        <tr>
        <td>
            <h4 class="h5 mt-3 mb-2">
            <a href="{{ $rep['trib_rep_url'] }}" target="_blank"> Acórdão  {{ $rep['trib_rep_acordao'] }}/{{ $rep['trib_rep_ano'] }} (resposta a consulta) - {{ $rep['trib_rep_orgao'] }}</a>
            </h4>
            <p class="d-sm-block" style="font-weight: bold;">
                {!! $rep['trib_rep_texto'] !!}
            </p>
            <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">{{ $rep['trib_rep_funcao'] }}: {{ $rep['trib_rep_autor'] }} | Data da sessão: {{ $rep['trib_rep_data'] }}.</span>
        </td>
        </tr>
@endforeach
