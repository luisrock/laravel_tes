@foreach ($output['tese']['hits'] as $rep)
    <tr>
        <td class="tw-block tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-6 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all">
            <div class="tw-flex tw-items-start tw-justify-between tw-gap-4 tw-mb-4">
                <h4 class="tw-text-lg tw-font-semibold tw-text-brand-700">
                    <a href="javascript:void(0);" class="tw-cursor-default">
                        {{ $rep['trib_rep_titulo'] }}
                    </a>
                </h4>
                <a href="{{ $rep['trib_rep_url'] }}" target="_blank" class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600 hover:tw-bg-slate-200 hover:tw-text-brand-700 tw-transition-colors">
                    {{ $rep['trib_rep_processo'] }} <i class="fa fa-external-link tw-ml-1"></i>
                </a>
            </div>

            <div class="tw-space-y-4">
                <div class="tw-bg-slate-50 tw-rounded-md tw-p-3 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-1">Questão</span>
                    <p class="tw-text-slate-700 tw-text-sm md:tw-text-base">
                        {{ $rep['trib_rep_tema'] }}
                    </p>
                </div>

                <div>
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-1">Tese</span>
                    <p class="tw-font-medium tw-text-slate-900 tw-text-base md:tw-text-lg tw-leading-relaxed">
                        {{ $rep['trib_rep_tese'] }}
                    </p>
                </div>

                @if (!empty($rep['trib_rep_obs']))
                <div class="tw-text-sm tw-text-slate-500 tw-italic tw-border-l-4 tw-border-slate-300 tw-pl-3">
                    Obs: {{ $rep['trib_rep_obs'] }}
                </div>
                @endif
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-3 tw-mt-5 tw-pt-4 tw-border-t tw-border-slate-100">
                <button class="btn-copy-text tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-1 focus:tw-ring-brand-500 tw-transition-colors" title="Copiar texto">
                   <i class="fa fa-copy tw-mr-1.5"></i> Copiar
                </button>

                <div class="tw-text-right">
                    <div class="tw-text-sm tw-font-medium tw-text-slate-700">
                        Situação: <span class="tw-text-brand-600">{{ $rep['trib_rep_situacao'] }}</span>
                    </div>
                    <div class="tw-flex tw-items-center tw-justify-end tw-gap-2 tw-text-xs tw-text-slate-400 tw-mt-1">
                        <span>Relator: {{ $rep['trib_rep_relator'] }}</span>
                        <span>•</span>
                        <span>Atualizado em {{ $rep['trib_rep_data'] }}</span>
                    </div>
                </div>
            </div>

            <span class="tes-clear tes-text-to-be-copied tw-hidden" data-spec="trim">
                {{ $rep['trib_rep_titulo'] }}. QUESTÃO: {{ $rep['trib_rep_tema'] }} TESE: {{ $rep['trib_rep_tese'] }} 
                @if (!empty($rep['trib_rep_obs']))OBS: {{ $rep['trib_rep_tese'] }}@endif 
                {{ $rep['trib_rep_processo'] }}, {{ $rep['trib_rep_relator'] }}. 
                SITUAÇÃO: {{ $rep['trib_rep_situacao'] }} (última atualização em {{ $rep['trib_rep_data'] }})
            </span>
        </td>
    </tr>
@endforeach
