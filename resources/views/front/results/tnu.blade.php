@extends('front.content_results')

@section('sumulas_total_text')
    @if (empty($output['sumula']['total']))
        <span class="tw-text-slate-600">Nenhum enunciado encontrado</span>
    @elseif ($output['sumula']['total'] == 1)
        <span class="tw-font-bold tw-text-brand-700">1</span> <span class="tw-text-slate-600">enunciado encontrado</span>
    @else
        <span class="tw-font-bold tw-text-brand-700">{{ $output['sumula']['total'] }}</span> <span class="tw-text-slate-600">enunciados encontrados</span>
    @endif
    <span class="tw-text-slate-600">na TNU para</span> <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
@endsection

@section('sumulas_inner_table')
    @include('front.results.inners.tnu_sum')
@endsection

@section('teses_total_text')
    @if (empty($output['tese']['total']))
        <span class="tw-text-slate-600">Nenhum tema representativo encontrado</span>
    @elseif ($output['tese']['total'] == 1)
        <span class="tw-font-bold tw-text-brand-700">1</span> <span class="tw-text-slate-600">tema representativo encontrado</span>
    @else
        <span class="tw-font-bold tw-text-brand-700">{{ $output['tese']['total'] }}</span> <span class="tw-text-slate-600">temas representativos encontrados</span>
    @endif
    <span class="tw-text-slate-600">na TNU para</span> <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
@endsection

@section('teses_inner_table')
  @include('front.results.inners.tnu_rep')
@endsection
