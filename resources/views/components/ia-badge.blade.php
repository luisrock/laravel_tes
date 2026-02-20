@props(['size' => 'sm', 'url' => null])

@php
    $classes = match($size) {
        'xs' => 'tw-px-2 tw-py-0.5 tw-text-[10px]',
        'sm' => 'tw-px-2.5 tw-py-1 tw-text-xs',
        'md' => 'tw-px-3 tw-py-1.5 tw-text-sm',
        default => 'tw-px-2.5 tw-py-1 tw-text-xs',
    };
    // Estética Premium: Destaque de Assinatura
    $baseStyle = "tw-inline-flex tw-items-center tw-rounded-full tw-font-bold tw-text-white tw-bg-gradient-to-r tw-from-amber-500 tw-to-orange-500 tw-shadow-sm hover:tw-shadow-md hover:tw-from-amber-600 hover:tw-to-orange-600 hover:tw-scale-[1.03] hover:-tw-translate-y-0.5 tw-transition-all tw-duration-300 {$classes}";
@endphp

@if($url)
<a href="{{ $url }}#ai-premium-box" class="{{ $baseStyle }}" title="Ver análise com Inteligência Artificial">
    <i class="fa fa-robot tw-text-amber-100 tw-mr-1.5 tw-text-[0.9em]"></i>
    Decifrando a tese
</a>
@else
<span class="{{ $baseStyle }}" title="Análise com Inteligência Artificial Disponível">
    <i class="fa fa-robot tw-text-amber-100 tw-mr-1.5 tw-text-[0.9em]"></i>
    Decifrando a tese
</span>
@endif
