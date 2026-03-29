@props(['item'])

@php
    $c = $item->content;
    $type = $item->content_type;
    $trib = $item->tribunal;        // lowercase
    $tribLabel = $item->tribunal_upper; // uppercase
    $url = $item->url;

    if ($type === 'tese') {
        // Normaliza campos por tribunal
        $numero   = $c->numero ?? null;
        $teseText = $c->tese_texto ?? $c->texto ?? $c->tese ?? null;
        $temaText = $c->tema_texto ?? $c->tema ?? null;
        $orgao    = $c->orgao ?? $c->titulo ?? null;
        $relator  = $c->relator ?? null;
        $data     = $c->aprovadaEm ?? $c->atualizadaEm ?? $c->julgadoEm ?? null;
        $situacao = $c->situacao ?? null;
        $tipo     = $c->tipo ?? null; // TST: PN|OJ|TV

        $tituloCard = $orgao && $trib === 'stj'
            ? "Tema/Repetitivo {$numero}"
            : "Tema {$numero}";
        $temaLabel  = $trib === 'stj' ? 'Questão' : 'Tema';
        $copyText   = "TEMA {$numero}: " . ($temaText ?? '') . ' TESE: ' . ($teseText ?? '');
    } else {
        // Súmula
        $numero    = $c->numero ?? null;
        $titulo    = $c->titulo ?? null;
        $teseText  = $c->texto ?? null;
        $temaText  = null;
        $temaLabel = null;
        $orgao     = null;
        $relator   = null;
        $data      = $c->aprovadaEm ?? $c->publicadaEm ?? $c->julgadaEm ?? null;
        $situacao  = null;
        $tipo      = null;
        $isCancelada = (bool) ($c->isCancelada ?? false);
        $isVinculante = (bool) ($c->is_vinculante ?? false);
        $tituloCard  = $titulo ?? "Súmula nº {$numero}";
        $copyText    = ($titulo ? $titulo . '. ' : '') . ($teseText ?? '');
    }
@endphp

@if ($c === null)
    <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-text-sm tw-text-slate-400 tw-italic">
        Conteúdo não disponível.
    </div>
@else
    <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-6 hover:tw-border-brand-300 hover:tw-shadow-sm tw-transition-all">

        {{-- Header --}}
        <div class="tw-flex tw-items-start tw-justify-between tw-gap-3 tw-mb-4">
            <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-bold tw-bg-brand-100 tw-text-brand-800 tw-uppercase">
                    {{ $tribLabel }}
                </span>
                <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-{{ $type === 'tese' ? 'blue' : 'brand' }}-100 tw-text-{{ $type === 'tese' ? 'blue' : 'brand' }}-800">
                    {{ $type === 'tese' ? 'Tese' : 'Súmula' }}
                </span>
                @if ($tipo && $trib === 'tst')
                    <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600">
                        {{ $tipo }}
                    </span>
                @endif
                @if (isset($isVinculante) && $isVinculante)
                    <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-amber-100 tw-text-amber-800">
                        Vinculante
                    </span>
                @endif
                @if (isset($isCancelada) && $isCancelada)
                    <span class="tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-red-100 tw-text-red-700">
                        Cancelada
                    </span>
                @endif
            </div>
            @if ($situacao)
                <span class="tw-text-xs tw-text-slate-500 tw-shrink-0 tw-font-medium">{{ $situacao }}</span>
            @endif
        </div>

        {{-- Título --}}
        <h4 class="tw-text-lg tw-font-semibold tw-text-brand-700 tw-mb-3">
            @if ($url)
                <a href="{{ $url }}" class="hover:tw-text-brand-900 hover:tw-underline tw-transition-colors text-decoration-none">
                    {{ $tituloCard }}
                </a>
            @else
                {{ $tituloCard }}
            @endif
        </h4>

        {{-- Corpo --}}
        <div class="tw-space-y-3">
            @if ($temaText)
                <div class="tw-bg-slate-50 tw-rounded-md tw-p-3 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-1">{{ $temaLabel }}</span>
                    <p class="tw-text-slate-700 tw-text-sm">{{ $temaText }}</p>
                </div>
            @endif

            @if ($teseText)
                <div>
                    <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mb-1">
                        {{ $type === 'tese' ? 'Tese' : 'Enunciado' }}
                    </span>
                    <p class="tw-font-medium tw-text-slate-900 tw-text-sm md:tw-text-base tw-leading-relaxed">{{ $teseText }}</p>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-3 tw-mt-4 tw-pt-4 tw-border-t tw-border-slate-100">
            <div class="tw-flex tw-items-center tw-gap-2">
                <button
                    class="btn-copy-text tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-brand-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-1 focus:tw-ring-brand-500 tw-transition-colors"
                    title="Copiar texto"
                >
                    <i class="fa fa-copy tw-mr-1.5"></i> Copiar
                </button>
                @auth
                    <x-save-to-collection-btn :type="$type" :tribunal="$trib" :contentId="$item->content_id" />
                @endauth
            </div>

            @if ($relator || $data || $orgao)
                <span class="tw-text-xs tw-text-slate-500 tw-italic">
                    @if ($orgao && $type === 'tese' && $trib === 'stj')
                        {{ $orgao }}@if($data),@endif
                    @endif
                    @if ($relator)
                        {{ $relator }}@if($data),@endif
                    @endif
                    @if ($data)
                        {{ $data }}
                    @endif
                </span>
            @endif
        </div>

        {{-- Hidden text para cópia --}}
        <span class="tes-clear tes-text-to-be-copied tw-hidden" data-spec="trim">{{ $copyText }}</span>

    </div>
@endif
