@foreach ($output['tese']['hits'] as $rep)
    <tr>
        <td class="tw-block tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-6 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all">
            <h4 class="tw-text-lg tw-font-semibold tw-text-brand-700 tw-mb-3">
                <a href="{{ $rep['trib_rep_url'] }}" target="_blank" class="hover:tw-text-brand-900 hover:tw-underline tw-transition-colors">
                    {{ $rep['trib_rep_tipo'] }} nº {{ $rep['trib_rep_numero'] }} <i class="fa fa-external-link tw-ml-1 tw-text-sm"></i>
                </a>
            </h4>
            
            <div class="tw-mb-4">
                 <span class="tw-inline-block tw-bg-slate-100 tw-rounded-md tw-px-2 tw-py-1 tw-text-sm tw-text-slate-600 tw-font-medium">
                    {{ $rep['trib_rep_tema'] }}
                </span>
            </div>

            <div class="tw-prose tw-prose-slate tw-max-w-none tw-mb-4">
                <p class="tw-font-medium tw-text-slate-900">
                    {{ $rep['trib_rep_texto'] }}
                </p>
            </div>

            <div class="tw-flex tw-items-center tw-justify-start tw-mt-4 tw-pt-4 tw-border-t tw-border-slate-100">
                <button class="btn-copy-text tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-1 focus:tw-ring-brand-500 tw-transition-colors" title="Copiar texto">
                   <i class="fa fa-copy tw-mr-1.5"></i> Copiar
                </button>
            </div>

            <span class="tes-clear tes-text-to-be-copied tw-hidden" data-spec="trim">
                {{ $rep['trib_rep_tipo'] }} nº {{ $rep['trib_rep_numero'] }}. {{ $rep['trib_rep_tema'] }}. TEXTO: {{ $rep['trib_rep_texto'] }}
            </span>
        </td>
    </tr>
@endforeach
